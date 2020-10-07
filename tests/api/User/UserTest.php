<?php

namespace App\Tests\User;

use App\Controller\Admin\UserCrudController;
use App\DBAL\Types\Enum\AccessEnumType;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
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
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/self_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
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

        $schema = $I->getSchema('user/bootstrap.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function removeShareUser()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->createTeam($domainAdmin);
        $I->addUserToTeam($team, $user);

        $item = $I->createTeamItem($team, $domainAdmin);

        $I->symfonyAuth($domainAdmin);
        $I->symfonyRequest(
            'DELETE',
            sprintf('/admin/?action=delete&entity=User&id=%s', $user->getId()->toString()),
            ['_method' => 'DELETE', 'delete_form' => ['_easyadmin_delete_flag' => 1]]
        );

        foreach ($item->getSharedItems() as $item) {
            $I->dontSeeInDatabase('item', ['id' => $item->getId()->toString()]);
        }
    }

    /** @test */
    public function removeUser()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var User $anonymous */
        $anonymous = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
        ]);

        $I->login($user);
        $I->sendPOST('list', [
            'label' => 'Remove list',
        ]);
        [$directoryId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST('items', [
            'listId' => $directoryId,
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$itemId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST('items', [
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$defaultItemId] = $I->grabDataFromResponseByJsonPath('$.id');

        /** @var Item $personalItem */
        $personalItem = $I->have(Item::class, [
            'parent_list' => $user->getDefaultDirectory(),
            'owner' => $user,
        ]);
        /** @var Item $shareItem */
        $shareItem = $I->have(Item::class, [
            'parent_list' => $member->getDefaultDirectory(),
            'owner' => $member,
            'original_item' => $personalItem,
            'access' => AccessEnumType::TYPE_READ,
            'cause' => Item::CAUSE_SHARE,
        ]);
        /** @var Item $anonymousShareItem */
        $anonymousShareItem = $I->have(Item::class, [
            'parent_list' => $anonymous->getDefaultDirectory(),
            'owner' => $anonymous,
            'original_item' => $personalItem,
            'access' => AccessEnumType::TYPE_READ,
            'cause' => Item::CAUSE_SHARE,
        ]);

        $I->sendPOST('/teams', [
            'title' => uniqid(),
            'icon' => null,
        ]);
        [$teamId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST(sprintf('teams/%s/lists', $teamId), [
            'label' => uniqid(),
        ]);
        [$listTeamId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST(sprintf('teams/%s/members/%s', $teamId, $member->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_MEMBER,
        ]);

        /** @var Team $team */
        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $teamItem = $I->createTeamItem($team, $user);

        $I->symfonyAuth($domainAdmin);
        $crudId = substr(sha1(getenv('APP_SECRET').UserCrudController::class), 0, 7);

        $I->symfonyRequest(
            'DELETE',
            sprintf('/admin?crudAction=delete&entityId=%s&crudId=%s', $user->getId()->toString(), $crudId),
            ['_method' => 'DELETE', 'delete_form' => ['_easyadmin_delete_flag' => 1], 'token' => $I->generateCsrf('ea-delete')]
        );

        $I->dontSeeInDatabase('item', ['id' => $itemId]);
        $I->dontSeeInDatabase('item', ['id' => $defaultItemId]);
        $I->dontSeeInDatabase('item', ['id' => $personalItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $shareItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $anonymousShareItem->getId()->toString()]);
        $I->dontSeeInDatabase('directory', ['id' => $directoryId]);
        $I->dontSeeInDatabase('directory', ['id' => $user->getDefaultDirectory()->getId()->toString()]);

        $I->seeInDatabase('item', ['id' => $teamItem->getId()->toString(), 'owner_id' => $member->getId()->toString()]);
        $I->seeInDatabase('groups', ['id' => $teamId]);
        $I->seeInDatabase('directory', ['id' => $listTeamId]);
        $I->seeInDatabase('fos_user', ['id' => $anonymous->getId()->toString()]);
    }
}
