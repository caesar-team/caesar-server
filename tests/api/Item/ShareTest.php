<?php

namespace App\Tests\Item;

use App\Entity\Item;
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
    public function createKeypairItems()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $memberRead */
        $memberRead = $this->tester->have(User::class);
        /** @var User $someMember */
        $someMember = $this->tester->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $memberRead->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $someMember->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        [$keypairItemId1] = $I->grabDataFromResponseByJsonPath('$[1].keypairId');
        [$keypairItemId2] = $I->grabDataFromResponseByJsonPath('$[2].keypairId');

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId1]);
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId2]);

        $I->sendDELETE(sprintf('items/%s', $keypairItemId1));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->dontSeeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId1]);
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId2]);
    }

    /** @test */
    public function shareItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);
        /** @var User $otherMember */
        $otherMember = $this->tester->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($member);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
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

        $schema = $I->getSchema('item/shares.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}
