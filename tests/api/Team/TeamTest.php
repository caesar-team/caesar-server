<?php

namespace App\Tests\Team;

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
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/team.json'));

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
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/teams.json'));
    }

    /** @test */
    public function getListsOfTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $team = $I->createTeam($user);
        $item = $I->createTeamItem($team, $user);

        $I->login($user);
        $I->sendGET(sprintf('/teams/%s/lists', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseContains($item->getId()->toString());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/team_lists.json'));
    }

    /** @test */
    public function editTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $admin */
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);

        $team = $I->createTeam($admin);
        $otherTeam = $I->createTeam($user);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s', $team->getId()->toString()), [
            'title' => 'Edited title',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($team->getId()->toString());

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
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->sendDELETE(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function pinnedTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $admin */
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user, UserTeam::USER_ROLE_ADMIN);

        $I->login($user);
        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => true]);

        $I->sendPOST(sprintf('teams/%s/pin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => true]);

        $I->sendPOST(sprintf('teams/%s/unpin', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => false]);

        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => false]);

        $I->login($admin);
        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => true]);

        $I->sendPOST(sprintf('teams/%s/leave', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET(sprintf('teams/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['pinned' => false]);
    }
}
