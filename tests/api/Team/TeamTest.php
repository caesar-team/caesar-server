<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class TeamTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('/teams', [
            'title' => 'My test team',
            'icon' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->login($admin);
        $I->sendPOST('/teams', [
            'title' => 'My test team',
            'icon' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $team = $I->createTeam($user);

        $I->login($user);
        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseContains($team->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getTeams()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $team = $I->createTeam($user);

        $I->login($user);
        $I->sendGET('teams');
        $I->seeResponseContains($team->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/teams.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function editTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseContains($team->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function deleteTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->sendDELETE(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
