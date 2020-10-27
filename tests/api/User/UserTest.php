<?php

namespace App\Tests\User;

use App\Controller\Admin\UserCrudController;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use App\Tests\Helper\Doctrine;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UserTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory|Doctrine
     */
    protected ApiTester $tester;

    /** @test */
    public function createUser()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('user', [
            'email' => 'som@email',
            'plainPassword' => '',
            'encryptedPrivateKey' => '',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'code' => 400,
            'message' => 'Validation Failed',
            'errors' => [
                'children' => [],
            ],
        ]);
    }

    /** @test */
    public function getSelfInfo()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/self_user.json'));
    }

    /** @test */
    public function bootstrap()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/bootstrap.json'));
    }

    /** @test */
    public function removeUser()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $user */
        $user = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $anonymous */
        $anonymous = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
        ]);

        /** @var Directory $directory */
        $directory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
            'user' => $user,
        ]);
        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $directory,
        ]);
        $defaultItem = $I->createUserItem($user);
        $personalItem = $I->createUserItem($user);
        $shareItem = $I->createKeypairItem($member, $personalItem);
        $anonymousShareItem = $I->createKeypairItem($anonymous, $personalItem);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);
        $teamItem = $I->createTeamItem($team, $user);
        /** @var Directory $teamList */
        $teamList = $I->have(Directory::class, [
            'parent_list' => $team->getLists(),
            'team' => $team,
        ]);

        $I->symfonyAuth($domainAdmin);
        $I->deleteFromAdmin(UserCrudController::class, $user->getId()->toString());

        $I->dontSeeInDatabase('item', ['id' => $item->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $defaultItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $personalItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $shareItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $anonymousShareItem->getId()->toString()]);
        $I->dontSeeInDatabase('directory', ['id' => $directory->getId()->toString()]);
        $I->dontSeeInDatabase('directory', ['id' => $user->getDefaultDirectory()->getId()->toString()]);

        $I->seeInDatabase('item', ['id' => $teamItem->getId()->toString(), 'owner_id' => $member->getId()->toString()]);
        $I->seeInDatabase('groups', ['id' => $team->getId()->toString()]);
        $I->seeInDatabase('directory', ['id' => $teamList->getId()->toString()]);
        $I->seeInDatabase('fos_user', ['id' => $anonymous->getId()->toString()]);
    }
}
