<?php namespace App\Tests;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class ShareTest extends Unit
{
    protected ApiTester $tester;

    protected function _before()
    {
        $this->tester->mockRabbitMQProducer($this->makeEmpty(Producer::class));
    }

    public function testItemBatchShare()
    {
        /** @var User $admin */
        $admin = $this->tester->have(User::class);
        $user = $this->tester->have(User::class);
        /** @var Team $team */
        $team = $this->tester->have(Team::class);
        $this->tester->have(UserTeam::class, [
            'user' => $admin,
            'team' => $team
        ]);

        // Add user to admin team
        $this->tester->have(UserTeam::class, [
            'user' => $user,
            'team' => $team,
            'user_role' => UserTeam::USER_ROLE_MEMBER
        ]);

        /** @var Item $item */
        $item = $this->tester->have(Item::class, [
            'owner' => $user,
            'team' => $team,
            'parent_list' => $admin->getLists()
        ]);


        $this->tester->login($user);
        $this->tester->sendPOST('/item/batch/share',

            $jayParsedAry = [
                'originalItems' => [
                    [
                        'originalItem' => $item->getId()->toString(),
                        'items' => [
                            [
                                'userId' => $admin->getId()->toString(),
                                'teamId' => $team->getId()->toString(),
                                'secret' => 'Some secret string, it doesn`t matter for backend',
                                'access' => AccessEnumType::TYPE_READ,
                                'cause' => Item::CAUSE_INVITE
                            ],
                        ]
                    ]
                ]
            ]

        );

        $this->tester->seeResponseCodeIs(HttpCode::OK);

        $schema = $this->tester->getSchema('item_batch_share.json');
        $this->tester->seeResponseIsValidOnJsonSchemaString($schema);
    }
}