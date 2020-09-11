<?php

namespace App\Tests\Item;

use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class FavoriteTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function toggleFavorite()
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
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.favorite'));
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendGET(sprintf('/items?listId=%s', $user->getLists()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => true]]);

        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.favorite'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendGET(sprintf('/items?listId=%s', $user->getLists()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => false]]);
    }

    /** @test */
    public function toggleTeamFavorite()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        /** @var Item $item */
        $item = $I->createTeamItem($team, $user);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.favorite'));
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendGET(sprintf('/items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => true]]);

        $I->login($member);
        $I->sendGET(sprintf('/items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => false]]);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.favorite'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendGET(sprintf('/items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => false]]);
    }

    /** @test */
    public function getListFavoriteItems()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $user */
        $member = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getDefaultDirectory(),
        ]);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        /** @var Item $teamItem */
        $teamItem = $I->createTeamItem($team, $user);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST(sprintf('/items/%s/favorite', $teamItem->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET('/items/favorite/');
        $I->canSeeResponseContains($item->getId()->toString());
        $I->cantSeeResponseContains($teamItem->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString(
            $I->getSchema('item/item_list.json')
        );

        $I->sendGET(sprintf('/items/favorite/%s', $team->getId()->toString()));
        $I->canSeeResponseContains($teamItem->getId()->toString());
        $I->cantSeeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString(
            $I->getSchema('item/item_list.json')
        );

        $I->login($member);
        $I->sendGET(sprintf('/items/favorite/%s', $team->getId()->toString()));
        $I->cantSeeResponseContains($teamItem->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString(
            $I->getSchema('item/item_list.json')
        );
    }
}
