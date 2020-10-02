<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ListTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getSelfLists()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getInbox(),
        ]);

        $I->login($user);

        $I->sendPOST('/items/batch/share',
            [
                'originalItems' => [
                    [
                        'originalItem' => $item->getId()->toString(),
                        'items' => [
                            [
                                'userId' => $member->getId()->toString(),
                                'secret' => 'Some secret string, it doesn`t matter for backend',
                                'access' => AccessEnumType::TYPE_READ,
                                'cause' => Item::CAUSE_SHARE,
                            ],
                        ],
                    ],
                ],
            ]
        );

        $I->sendGET('/list');
        $I->seeResponseContainsJson(['type' => Directory::LIST_INBOX]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_TRASH]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_DEFAULT]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/lists.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->login($member);

        $I->sendGET('/list');
        $I->seeResponseContainsJson(['type' => Directory::LIST_INBOX]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_TRASH]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_DEFAULT]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/lists.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function sortList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('list', ['label' => '4']);
        [$fourthDirectory] = $I->grabDataFromResponseByJsonPath('$.id');
        $I->sendPOST('list', ['label' => '3']);
        [$thirdDirectory] = $I->grabDataFromResponseByJsonPath('$.id');
        $I->sendPOST('list', ['label' => '2']);
        [$secondDirectory] = $I->grabDataFromResponseByJsonPath('$.id');
        $I->sendPOST('list', ['label' => '1']);
        [$firstDirectory] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendGET('/list');
        self::assertEquals(
            [$firstDirectory],
            $I->grabDataFromResponseByJsonPath('$.[0].id')
        );
        self::assertEquals(
            [$secondDirectory],
            $I->grabDataFromResponseByJsonPath('$.[1].id')
        );
        self::assertEquals(
            [$thirdDirectory],
            $I->grabDataFromResponseByJsonPath('$.[2].id')
        );
        self::assertEquals(
            [$fourthDirectory],
            $I->grabDataFromResponseByJsonPath('$.[3].id')
        );
    }
}
