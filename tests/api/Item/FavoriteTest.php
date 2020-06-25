<?php

namespace App\Tests\Item;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class FavoriteTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    protected function _before()
    {
        $this->tester->mockRabbitMQProducer($this->makeEmpty(Producer::class));
    }

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

        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.favorite'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getListFavoriteItems()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $user */
        $member = $I->have(User::class);

        /** @var Directory $defaultList */
        $defaultList = $user->getLists()->getChildLists()->first();

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $defaultList,
        ]);

        $team = $I->createTeam($user);

        /** @var Item $item */
        $teamItem = $this->tester->have(Item::class, [
            'owner' => $user,
            'team' => $team,
            'parent_list' => $team->getDefaultDirectory(),
        ]);

        $I->login($user);
        $I->shareItemToUser($item, $member);
        $I->shareItemToUser($teamItem, $member, $team);
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
    }
}
