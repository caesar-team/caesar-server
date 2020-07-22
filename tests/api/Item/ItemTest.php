<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ItemTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendGET(sprintf('/items/%s', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getItemList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendGET('/items');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendGET(sprintf('/items?listId=%s', $user->getLists()->getId()->toString()));
        $I->seeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/item_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function sortItemList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Item $thirdItem */
        $thirdItem = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);
        sleep(1);
        /** @var Item $secondItem */
        $secondItem = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);
        sleep(1);
        /** @var Item $firstItem */
        $firstItem = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);

        $I->login($user);

        $I->sendGET(sprintf('/items?listId=%s', $user->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        self::assertEquals(
            [$firstItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[0].id')
        );
        self::assertEquals(
            [$secondItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[1].id')
        );
        self::assertEquals(
            [$thirdItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[2].id')
        );

        $I->updateInDatabase('item', ['last_updated' => (new \DateTimeImmutable('+10 minute'))->format('Y-m-d H:i:s')], ['id' => $thirdItem->getId()->toString()]);
        $I->sendGET(sprintf('/items?listId=%s', $user->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        self::assertEquals(
            [$firstItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[1].id')
        );
        self::assertEquals(
            [$secondItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[2].id')
        );
        self::assertEquals(
            [$thirdItem->getId()->toString()],
            $I->grabDataFromResponseByJsonPath('$.[0].id')
        );
    }

    /** @test */
    public function createCredItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var Directory $directory */
        $directory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST('items', [
            'listId' => 'invalid-uuid',
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendPOST('items', [
            'listId' => $directory->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/create_item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function editItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
        ]);
        /** @var Item $item */
        $otherItem = $I->have(Item::class);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'item' => [
                'secret' => 'secret-edit',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPATCH(sprintf('/items/%s', $otherItem->getId()), [
            'item' => [
                'secret' => 'secret-edit',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    /** @test */
    public function deleteItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
        ]);

        /** @var Item $item */
        $otherItem = $I->have(Item::class);

        $I->login($user);
        $I->sendDELETE(sprintf('/items/%s', $item->getId()));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('You can fully delete item only from trash.');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()), [
            'listId' => $user->getTrash()->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendDELETE(sprintf('/items/%s', $item->getId()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendDELETE(sprintf('/items/%s', $otherItem->getId()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    /** @test */
    public function moveItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Directory $otherDirectory */
        $otherDirectory = $I->have(Directory::class);

        /** @var Directory $directory */
        $directory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);

        /** @var Item $otherItem */
        $otherItem = $I->have(Item::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'parent_list' => $directory,
            'owner' => $user,
        ]);

        /** @var Directory $moveDirectory */
        $moveDirectory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()), [
            'listId' => $moveDirectory->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()), [
            'listId' => $otherDirectory->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('You are not owner of list');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $otherItem->getId()), [
            'listId' => $moveDirectory->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    /** @test */
    public function batchMoveItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Directory $otherDirectory */
        $otherDirectory = $I->have(Directory::class);
        /** @var Directory $directory */
        $directory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);
        /** @var Directory $moveDirectory */
        $moveDirectory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);
        /** @var Item $otherItem */
        $otherItem = $I->have(Item::class);
        /** @var Item $item */
        $item = $I->have(Item::class, [
            'parent_list' => $directory,
            'owner' => $user,
        ]);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $otherDirectory->getId()->toString()), [
            'items' => [$item->getId()->toString()],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $moveDirectory->getId()->toString()), [
            'items' => [$item->getId()->toString()],
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $otherDirectory->getId()->toString()), [
            'items' => [
                $item->getId()->toString(),
                $otherItem->getId()->toString(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }
}
