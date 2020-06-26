<?php

namespace App\Tests\Team;

use App\Entity\Item;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ListTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $team = $I->createTeam($user);
        /** @var Item $item */
        $item = $I->have(Item::class, [
            'owner' => $user,
            'parent_list' => $team->getDefaultDirectory(),
        ]);

        $I->login($user);
        $I->sendGET(sprintf('/teams/%s/lists', $team->getId()->toString()));
        $I->canSeeResponseContains($item->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team_lists.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
