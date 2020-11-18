<?php

namespace App\Tests\Item;

use App\Controller\Admin\ItemCrudController;
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

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendGET(sprintf('/items/%s', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/item.json'));

        $I->sendGET(sprintf('/items/%s/raws', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/item_raw.json'));
    }

    /** @test */
    public function getBatchItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $item = $I->createUserItem($user);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $userKeypairTeam = $I->createKeypairTeamItem($team, $user);
        $userKeypairItem = $I->createKeypairItem($member, $item);
        $memberKeypairTeam = $I->createKeypairTeamItem($team, $member);
        $teamItem = $I->createTeamItem($team, $user);

        $I->login($user);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $userKeypairTeam->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.keypairs', ['id' => $memberKeypairTeam->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.personals', ['id' => $teamItem->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.teams', ['id' => $teamItem->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.teams', ['id' => $item->getId()->toString()]);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/all_item.json'));

        $I->login($member);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $userKeypairItem->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['id' => $memberKeypairTeam->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.keypairs', ['id' => $userKeypairTeam->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.shares', ['id' => $item->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.teams', ['id' => $teamItem->getId()->toString()]);
    }

    /** @test */
    public function getFilteredAllItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);

        /** @var Item $item */
        $item2 = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);

        $I->updateInDatabase(
            'item',
            ['last_updated' => (new \DateTimeImmutable('+10 days'))->format('Y-m-d H:i:s')],
            ['id' => $item2->getId()->toString()]
        );

        $I->login($user);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item2->getId()->toString()]);

        $I->sendGET(sprintf('/items/all?lastUpdated=%s', time() + 3600));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item2->getId()->toString()]);
    }

    /** @test */
    public function getItemList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendGET('/items');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('This value should not be blank.');

        $I->sendGET(sprintf('/items?listId=%s', $user->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/item_list.json'));
    }

    /** @test */
    public function sortItemList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $thirdItem = $I->createUserItem($user);
        $secondItem = $I->createUserItem($user);
        $firstItem = $I->createUserItem($user);

        $this->modifyLastUpdatedItem(new \DateTime('+5 second'), $secondItem);
        $this->modifyLastUpdatedItem(new \DateTime('+10 second'), $firstItem);

        $I->login($user);

        $I->sendGET(sprintf('/items?listId=%s', $user->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseContainsJson([
            ['id' => $firstItem->getId()->toString()],
            ['id' => $secondItem->getId()->toString()],
            ['id' => $thirdItem->getId()->toString()],
        ]);

        $this->modifyLastUpdatedItem(new \DateTime('+10 minute'), $thirdItem);

        $I->sendGET(sprintf('/items?listId=%s', $user->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseContainsJson([
            ['id' => $thirdItem->getId()->toString()],
            ['id' => $firstItem->getId()->toString()],
            ['id' => $secondItem->getId()->toString()],
        ]);
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
            'title' => 'item title',
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendPOST('items', [
            'listId' => $directory->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'title' => 'item title',
            'favorite' => false,
            'meta' => [
                'attachCount' => 2,
            ],
            'raws' => uniqid(),
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/create_item.json'));

        $I->sendPOST('items', [
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'meta' => [
                'webSite' => 'http://examle.com',
            ],
            'title' => 'item title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['listId' => $user->getDefaultDirectory()->getId()->toString()]);
    }

    /** @test */
    public function editItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $item = $I->createUserItem($user);
        /** @var Item $item */
        $otherItem = $I->have(Item::class);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'secret' => 'secret-edit',
            'title' => 'item title (edited)',
            'meta' => [
                'attachCount' => 3,
                'webSite' => 'http://examle.com/login',
            ],
            'raws' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPATCH(sprintf('/items/%s', $otherItem->getId()), [
            'secret' => 'secret-edit',
            'title' => 'item title (edited)',
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /** @test */
    public function deleteItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $item = $I->createUserItem($user);

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
    }

    /** @test */
    public function moveItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $item = $I->createUserItem($user);

        /** @var Directory $moveDirectory */
        $moveDirectory = $I->have(Directory::class, ['parent_list' => $user->getLists()]);

        /** @var Directory $otherDirectory */
        $otherDirectory = $I->have(Directory::class);
        /** @var Item $otherItem */
        $otherItem = $I->have(Item::class);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()), [
            'listId' => $moveDirectory->getId(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeInDatabase('item', ['id' => $item->getId(), 'parent_list_id' => $moveDirectory->getId()]);

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
    }

    /** @test */
    public function batchMoveItems()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $item = $I->createUserItem($user);
        $item2 = $I->createUserItem($user);
        /** @var Directory $moveDirectory */
        $moveDirectory = $I->have(Directory::class, ['parent_list' => $user->getLists()]);

        /** @var Directory $otherDirectory */
        $otherDirectory = $I->have(Directory::class);
        /** @var Item $otherItem */
        $otherItem = $I->have(Item::class);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $otherDirectory->getId()), [
            'items' => [$item->getId()],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $moveDirectory->getId()), [
            'items' => [$item->getId(), $item2->getId()],
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeInDatabase('item', ['id' => $item->getId(), 'parent_list_id' => $moveDirectory->getId()]);
        $I->seeInDatabase('item', ['id' => $item2->getId(), 'parent_list_id' => $moveDirectory->getId()]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/batch/move/list/%s', $otherDirectory->getId()), [
            'items' => [
                $item->getId(),
                $otherItem->getId(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /** @test */
    public function removePersonalKeypairItem()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $shareUser */
        $shareUser = $I->have(User::class);

        $item = $I->createUserItem($user);

        $ownerKeypairItem = $I->createKeypairItem($user, $item);
        $userKeypairItem = $I->createKeypairItem($shareUser, $item);

        $I->symfonyAuth($domainAdmin);
        $I->deleteFromAdmin(ItemCrudController::class, $ownerKeypairItem->getId()->toString());

        $I->dontSeeInDatabase('item', ['id' => $userKeypairItem->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $item->getId()->toString()]);
    }

    /** @test */
    public function getBatchItems()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $item1 = $I->createUserItem($user);
        $item2 = $I->createUserItem($user);

        $I->login($user);
        $I->sendGET(sprintf('items/batch?items[]=%s&items[]=%s', $item1->getId()->toString(), $item2->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/batch_item.json'));
        $I->seeResponseContains($item1->getId()->toString());
        $I->seeResponseContains($item2->getId()->toString());
    }

    private function modifyLastUpdatedItem(\DateTimeInterface $dateTime, Item $item): void
    {
        $this->tester->updateInDatabase('item', ['last_updated' => $dateTime->format('Y-m-d H:i:s')], ['id' => $item->getId()->toString()]);
    }
}
