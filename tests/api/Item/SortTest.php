<?php

namespace App\Tests\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class SortTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function sortItem()
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
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/sort', $item->getId()->toString()), [
            'sort' => 1,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([1], $I->grabDataFromResponseByJsonPath('$.sort'));

        $schema = $I->getSchema('item/item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
