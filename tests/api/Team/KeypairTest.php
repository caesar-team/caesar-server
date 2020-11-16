<?php

namespace App\Tests\Team;

use App\Controller\Admin\ItemCrudController;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class KeypairTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createBatchKeypairItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $I->login($user);
        $I->sendPOST('items/batch/keypairs', [
            'items' => [
                [
                    'teamId' => $team->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'teamId' => $team->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'ownerId' => $member->getId()->toString(),
                    'teamId' => $team->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('items/batch/keypairs', [
            'items' => [
                [
                    'teamId' => $team->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /** @test */
    public function removeTeamKeypairItem()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var User $removeMember */
        $removeMember = $I->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);
        $I->addUserToTeam($team, $removeMember);

        $userTeam = $team->getUserTeamByUser($removeMember);

        $item = $I->createTeamItem($team, $removeMember);

        $keypairItem = $I->createKeypairTeamItem($team, $user);
        $memberKeypairItem = $I->createKeypairTeamItem($team, $member);
        $removeMemberKeypairItem = $I->createKeypairTeamItem($team, $removeMember);

        $I->symfonyAuth($domainAdmin);
        $I->deleteFromAdmin(ItemCrudController::class, $removeMemberKeypairItem->getId()->toString());

        $I->dontSeeInDatabase('user_group', ['id' => $userTeam->getId()->toString()]);
        $I->seeInDatabase('item', ['id' => $keypairItem->getId()->toString()]);
        $I->seeInDatabase('item', ['id' => $memberKeypairItem->getId()->toString()]);
        $I->seeInDatabase('item', ['id' => $item->getId()->toString()]);

        $I->deleteFromAdmin(ItemCrudController::class, $keypairItem->getId()->toString());
        $I->dontSeeInDatabase('user_group', ['id' => $user->getId()->toString()]);
        $I->seeInDatabase('groups', ['id' => $team->getId()->toString()]);
        $I->seeInDatabase('item', ['id' => $memberKeypairItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $keypairItem->getId()->toString()]);
    }
}
