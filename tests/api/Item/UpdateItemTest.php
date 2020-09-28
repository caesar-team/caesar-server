<?php

namespace App\Tests\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UpdateItemTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /**
     * @test
     */
    public function updateItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Item $originalItem */
        $originalItem = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->shareItemToUser($originalItem, $member);

        $I->login($member);
        $I->sendGET('/offered_item');
        [$itemId] = $I->grabDataFromResponseByJsonPath('$.personal.0.id');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $itemId), [
            'originalItem' => [
                'secret' => $originalItem->getSecret(),
            ],
            'item' => [
                'secret' => 'secret-edit',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/share_item_update.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
