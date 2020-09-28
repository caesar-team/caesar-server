<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ChildItemTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function removeChildItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $anonymous */
        $anonymous = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
        ]);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/child_item', $item->getId()->toString()), [
            'items' => [
                [
                    'userId' => $anonymous->getId()->toString(),
                    'secret' => 'secret',
                    'cause' => Item::CAUSE_SHARE,
                    'access' => AccessEnumType::TYPE_READ,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$childItem] = $I->grabDataFromResponseByJsonPath('$.items[0].id');

        $I->sendDELETE(sprintf('/child_item/%s', $childItem));

        $I->dontSeeInDatabase('item', ['id' => $childItem]);
        $I->dontSeeInDatabase('fos_user', ['id' => $anonymous->getId()->toString()]);

        $I->sendPOST(sprintf('/items/%s/child_item', $item->getId()->toString()), [
            'items' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => 'secret',
                    'cause' => Item::CAUSE_SHARE,
                    'access' => AccessEnumType::TYPE_READ,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$childItem] = $I->grabDataFromResponseByJsonPath('$.items[0].id');
        $I->sendDELETE(sprintf('/child_item/%s', $childItem));

        $I->seeInDatabase('fos_user', ['id' => $member->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $childItem]);
    }
}
