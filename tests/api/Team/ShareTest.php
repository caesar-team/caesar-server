<?php

namespace App\Tests\Team;

use App\DBAL\Types\Enum\NodeEnumType;
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
        $I->sendPOST('items', [
            'listId' => $team->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'secret' => uniqid(),
            'title' => 'item title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('items', [
            'listId' => $team->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'relatedItemId' => $item->getId()->toString(),
            'secret' => uniqid(),
            'title' => 'item title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('items', [
            'ownerId' => $user->getId()->toString(),
            'listId' => $user->getInbox()->getId()->toString(),
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'relatedItemId' => $item->getId()->toString(),
            'secret' => uniqid(),
            'title' => 'item title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$keypairItemId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId]);

        $I->sendDELETE(sprintf('items/%s', $keypairItemId));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->dontSeeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId]);
    }
}
