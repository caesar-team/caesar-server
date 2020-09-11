<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use App\DBAL\Types\Enum\NodeEnumType;
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
    public function newShareItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $memberRead */
        $memberRead = $this->tester->have(User::class);
        /** @var User $memberWrite */
        $memberWrite = $this->tester->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST('items', [
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_SYSTEM,
            'relatedItem' => $item->getId()->toString(),
            'secret' => uniqid(),
        ]);
        [$systemItemId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST(sprintf('/items/%s/child_item', $systemItemId), [
            'items' => [
                [
                    'userId' => $memberRead->getId()->toString(),
                    'secret' => 'Some secret',
                    'cause' => Item::CAUSE_SHARE,
                    'access' => AccessEnumType::TYPE_READ,
                ],
                [
                    'userId' => $memberWrite->getId()->toString(),
                    'secret' => 'Some secret',
                    'cause' => Item::CAUSE_SHARE,
                    'access' => AccessEnumType::TYPE_WRITE,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function createChildItemToItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/child_item', $item->getId()->toString()), [
            'items' => [
                [
                    'userId' => $member->getId()->toString(),
                    'teamId' => $team->getId()->toString(),
                    'secret' => 'some secret',
                    'cause' => Item::CAUSE_SHARE,
                    'link' => '',
                    'access' => AccessEnumType::TYPE_READ,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/linked_items_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function batchShare()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendPOST('/items/batch/share', [
            'originalItems' => [
                [
                    'originalItem' => $item->getId()->toString(),
                    'items' => [
                        [
                            'userId' => $member->getId()->toString(),
                            'teamId' => $team->getId()->toString(),
                            'secret' => 'some secret',
                            'cause' => Item::CAUSE_SHARE,
                            'link' => '',
                            'access' => AccessEnumType::TYPE_READ,
                        ],
                    ],
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/batch_share.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
