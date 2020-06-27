<?php

namespace App\Tests\Team;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class MemberTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getDefaultMembers()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $team = $I->have(Team::class, [
            'alias' => Team::DEFAULT_GROUP_ALIAS,
            'title' => Team::DEFAULT_GROUP_TITLE,
        ]);

        $I->addUserToTeam($team, $admin, UserTeam::USER_ROLE_ADMIN);

        $I->login($admin);
        $I->sendGET('/teams/default/members');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/members.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getTeamMembers()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);

        $I->login($otherUser);
        $I->sendGET(sprintf('teams/%s/members', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($user);
        $I->sendGET(sprintf('teams/%s/members', $team->getId()->toString()));
        $I->seeResponseContains($admin->getId()->toString());
        $I->seeResponseContains($user->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/members.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function addMemberToTeam()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->sendPOST(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/member.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function deleteMember()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $otherUser);

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->sendDELETE(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function editMember()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $otherUser);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        $I->login($admin);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/member.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}