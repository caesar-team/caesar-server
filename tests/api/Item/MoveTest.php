<?php

namespace App\Tests\Item;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class MoveTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function moveSharedItemToTeam()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $shareUser */
        $shareUser = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $member);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $shareUser->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$memberKeypairItemId] = $I->grabDataFromResponseByJsonPath('$[1].keypairId');
        [$shareUserkeypairItemId] = $I->grabDataFromResponseByJsonPath('$[2].keypairId');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()), [
            'listId' => $team->getDefaultDirectory()->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendPOST('items/batch/keypairs', [
            'items' => [
                [
                    'teamId' => $team->getId()->toString(),
                    'relatedItemId' => $item->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$teamKeypairItemId] = $I->grabDataFromResponseByJsonPath('$[0].id');

        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $teamKeypairItemId]);

        $I->login($member);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.shares', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $memberKeypairItemId]);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $teamKeypairItemId]);

        $I->login($shareUser);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.shares', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $shareUserkeypairItemId]);
        $I->dontSeeResponseByJsonPathContainsJson('$.keypairs', ['id' => $teamKeypairItemId]);
    }
}
