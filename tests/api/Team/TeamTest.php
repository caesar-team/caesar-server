<?php

namespace App\Tests\Team;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use Ramsey\Uuid\Uuid;

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
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
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

        $I->sendGET(sprintf('teams/%s', Uuid::uuid4()));
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /** test */
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
    public function getListsOfTeam()
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
        $otherTeam = $I->createTeam($user);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
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

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => $otherTeam->getTitle(),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
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
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->sendDELETE(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function pinnedTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user, UserTeam::USER_ROLE_ADMIN);

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/pin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->sendPOST(sprintf('teams/%s/pin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->sendPOST(sprintf('teams/%s/unpin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->sendPOST(sprintf('teams/%s/unpin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->login($admin);
        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([true], $I->grabDataFromResponseByJsonPath('$.pinned'));

        $I->sendPOST(sprintf('teams/%s/leave', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([false], $I->grabDataFromResponseByJsonPath('$.pinned'));
    }
}
