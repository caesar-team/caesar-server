<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UserTeamTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getUserTeams()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->createTeam($admin);

        $I->login($admin);
        $I->sendGET('/user/teams');
        $I->canSeeResponseContains($team->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/user_teams.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
