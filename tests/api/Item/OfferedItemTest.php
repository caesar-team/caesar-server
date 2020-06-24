<?php

namespace App\Tests\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class OfferedItemTest extends Unit
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
    public function personalShare()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->shareItemToUser($item, $member);

        $I->login($member);
        $I->sendGET('/offered_item');
        $I->seeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/offered_item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function teamShare()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($user);

        $I->addUserToTeam($team, $member);

        /** @var Item $item */
        $item = $this->tester->have(Item::class, [
            'owner' => $member,
            'team' => $team,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($member);
        $I->shareItemToUser($item, $user, $team);

        $I->login($user);
        $I->sendGET('/offered_item');
        $I->seeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/offered_item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
