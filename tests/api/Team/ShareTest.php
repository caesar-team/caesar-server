<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ShareTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function shareTeamItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $admin */
        $admin = $I->have(User::class);

        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $member);
        $item = $I->createTeamItem($team, $member);

        $I->login($admin);
        $I->sendPOST('items/batch/keypairs', [
            'items' => [
                [
                    'teamId' => $team->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'teamId' => $team->getId()->toString(),
                    'relatedItemId' => $item->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                ['userId' => $user->getId()->toString(), 'secret' => uniqid()],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$keypairItemId] = $I->grabDataFromResponseByJsonPath('$[0].keypairId');

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId]);

        $I->sendDELETE(sprintf('items/%s', $keypairItemId));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->dontSeeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId]);
    }
}
