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
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);

        $team = $I->have(Team::class, [
            'alias' => Team::DEFAULT_GROUP_ALIAS,
            'title' => Team::DEFAULT_GROUP_TITLE,
        ]);

        $I->addUserToTeam($team, $admin, UserTeam::USER_ROLE_ADMIN);

        $I->login($admin);
        $I->sendGET('/teams/default/members');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/members.json'));
    }

    /** @test */
    public function getTeamMembers()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $manager */
        $manager = $I->have(User::class, [
            'roles' => [User::ROLE_MANAGER],
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->createKeypairTeamItem($team, $admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $manager, UserTeam::USER_ROLE_ADMIN);

        $I->login($otherUser);
        $I->sendGET(sprintf('teams/%s/members', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($user);
        $I->sendGET(sprintf('teams/%s/members', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($admin->getId()->toString());
        $I->seeResponseContains($user->getId()->toString());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/members.json'));
        $I->seeResponseContainsJson(['userId' => $admin->getId()->toString(), 'hasKeypair' => true]);
        $I->seeResponseContainsJson(['userId' => $user->getId()->toString(), 'hasKeypair' => false]);

        $I->sendGET(sprintf('teams/%s/members?without_keypair=true', $team->getId()->toString()));
        $I->dontSeeResponseContains($admin->getId()->toString());
        $I->seeResponseContains($user->getId()->toString());
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($manager);
        $I->sendGET(sprintf('teams/%s/members', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function addMemberToTeam()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $admin */
        $admin = $I->have(User::class);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $domainAdmin);

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/members', $team->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_ADMIN,
            'secret' => uniqid(),
            'userId' => $otherUser->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->sendPOST(sprintf('teams/%s/members', $team->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_ADMIN,
            'secret' => uniqid(),
            'userId' => $otherUser->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/member.json'));

        $I->sendPOST(sprintf('teams/%s/members', $team->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_ADMIN,
            'secret' => uniqid(),
            'userId' => $otherUser->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->login($domainAdmin);
        $I->sendPOST(sprintf('teams/%s/members', $team->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_ADMIN,
            'secret' => uniqid(),
            'userId' => $member->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/member.json'));
    }

    /** @test */
    public function addBatchMembersToTeam()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/members/batch', $team->getId()->toString()), [
            'members' => [
                [
                    'teamRole' => UserTeam::USER_ROLE_ADMIN,
                    'secret' => uniqid(),
                    'userId' => $otherUser->getId()->toString(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->sendPOST(sprintf('teams/%s/members/batch', $team->getId()->toString()), [
            'members' => [
                [
                    'teamRole' => UserTeam::USER_ROLE_ADMIN,
                    'secret' => uniqid(),
                    'userId' => $user->getId()->toString(),
                ],
                [
                    'teamRole' => UserTeam::USER_ROLE_ADMIN,
                    'secret' => uniqid(),
                    'userId' => $user->getId()->toString(),
                ],
                [
                    'teamRole' => UserTeam::USER_ROLE_MEMBER,
                    'secret' => uniqid(),
                    'userId' => $member->getId()->toString(),
                ],
                [
                    'teamRole' => UserTeam::USER_ROLE_MEMBER,
                    'secret' => uniqid(),
                    'userId' => $otherUser->getId()->toString(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/members.json'));
    }

    /** @test */
    public function deleteMember()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $manager */
        $manager = $I->have(User::class, ['roles' => [User::ROLE_MANAGER]]);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $otherUser);
        $I->addUserToTeam($team, $manager, UserTeam::USER_ROLE_ADMIN);

        $I->login($manager);
        $I->sendDELETE(sprintf('teams/%s/members/%s', $team->getId()->toString(), $admin->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->sendDELETE(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function editMember()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $manager */
        $manager = $I->have(User::class, ['roles' => [User::ROLE_MANAGER]]);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $otherUser);

        $I->login($manager);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/members/%s', $team->getId()->toString(), $admin->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_MEMBER,
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($admin);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/members/%s', $team->getId()->toString(), $otherUser->getId()->toString()), [
            'teamRole' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('team/member.json'));
    }

    /** @test */
    public function leaveTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $userKeypair = $I->createKeypairTeamItem($team, $user);
        $memberKeypair = $I->createKeypairTeamItem($team, $member);
        $otherTeam = $I->createTeam($user);
        $otherKeypair = $I->createKeypairTeamItem($otherTeam, $user);

        $I->login($member);
        $I->sendPOST(sprintf('teams/%s/leave', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeInDatabase('item', ['id' => $userKeypair->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $memberKeypair->getId()->toString()]);

        $I->sendPOST(sprintf('teams/%s/leave', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeInDatabase('groups', ['id' => $team->getId()->toString()]);

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/leave', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInDatabase('groups', ['id' => $otherTeam->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $otherKeypair->getId()->toString()]);
    }
}
