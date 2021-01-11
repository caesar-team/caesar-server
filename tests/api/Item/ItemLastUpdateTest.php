<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ItemLastUpdateTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function refreshAfterCreate()
    {
        $I = $this->tester;

        $currentDate = new \DateTime('-2 days');

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        $I->login($user);
        $I->sendPOST('items', [
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'meta' => [
                'attachmentsCount' => 2,
                'title' => 'item title',
            ],
            'raws' => uniqid(),
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$itemId] = $I->grabDataFromResponseByJsonPath('$.id');
        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $itemId]);

        $I->sendPOST(sprintf('items/%s/share', $itemId), [
            'users' => [
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $itemId]);
    }

    /** @test */
    public function refreshAfterUpdateAndRemove()
    {
        $I = $this->tester;

        $currentDate = new \DateTime('-2 days');

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $otherMember */
        $otherMember = $I->have(User::class);
        $item = $I->createUserItem($user);

        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $item->getId()->toString()]);

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
                    'userId' => $otherMember->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$keypairItemId1] = $I->grabDataFromResponseByJsonPath('$[1].keypairId');
        [$keypairItemId2] = $I->grabDataFromResponseByJsonPath('$[2].keypairId');

        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $keypairItemId1]);
        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $keypairItemId2]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'secret' => 'secret-edit',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $item->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $keypairItemId1]);
        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $keypairItemId2]);

        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $item->getId()->toString()]);
        $I->updateInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s')], ['id' => $keypairItemId2]);

        $I->sendDELETE(sprintf('items/%s', $keypairItemId1));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $item->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['last_updated' => $currentDate->format('Y-m-d H:i:s'), 'id' => $keypairItemId2]);
    }
}
