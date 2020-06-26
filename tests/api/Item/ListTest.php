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
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ListTest extends Unit
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
    public function sortItem()
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
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/lists.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->login($member);

        $I->sendGET('/list');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/lists.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
