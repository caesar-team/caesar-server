<?php

namespace App\Tests\Team;

use App\Entity\Directory;
use App\Entity\Team;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ListTest extends Unit
{
    private const DEFAULT_LIST_NAME = 'New list';

    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createList()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, ['roles' => [User::ROLE_SUPER_ADMIN]]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);

        /** @var Team $otherTeam */
        $otherTeam = $I->createTeam($domainAdmin);

        $this->canCreateTeamList($teamAdmin, $team, self::DEFAULT_LIST_NAME);
        $this->createListValidate($teamAdmin, $team);
        $this->canCreateTeamList($domainAdmin, $team, uniqid());
        $this->canCreateTeamList($domainAdmin, $otherTeam, uniqid());
        $this->cantAccessToCreateTeamList($superAdmin, $team);
        $this->cantAccessToCreateTeamList($member, $team);
        $this->cantAccessToCreateTeamList($teamAdmin, $otherTeam);
    }

    /** @test */
    public function editList()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, ['roles' => [User::ROLE_SUPER_ADMIN]]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        /** @var Team $otherTeam */
        $otherTeam = $I->createTeam($domainAdmin);

        /** @var Directory $list */
        $list = $I->have(Directory::class, [
            'label' => self::DEFAULT_LIST_NAME,
            'team' => $team,
            'parent_list' => $team->getLists(),
        ]);
        /** @var Directory $otherList */
        $otherList = $I->have(Directory::class, [
            'label' => self::DEFAULT_LIST_NAME,
            'team' => $otherTeam,
            'parent_list' => $otherTeam->getLists(),
        ]);

        $this->editListValidate($teamAdmin, $list);
        $this->canEditTeamList($teamAdmin, $list, self::DEFAULT_LIST_NAME);
        $this->canEditTeamList($domainAdmin, $list, uniqid());
        $this->cantAccessToEditTeamList($teamAdmin, $team->getDefaultDirectory());
        $this->cantAccessToEditTeamList($superAdmin, $list);
        $this->cantAccessToEditTeamList($member, $list);
        $this->cantAccessToEditTeamList($teamAdmin, $otherList);
    }

    /** @test */
    public function deleteList()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, ['roles' => [User::ROLE_SUPER_ADMIN]]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        /** @var Team $otherTeam */
        $otherTeam = $I->createTeam($domainAdmin);

        /** @var Directory $list */
        $list = $I->have(Directory::class, [
            'label' => self::DEFAULT_LIST_NAME,
            'team' => $team,
            'parent_list' => $team->getLists(),
        ]);
        /** @var Directory $otherList */
        $otherList = $I->have(Directory::class, [
            'label' => self::DEFAULT_LIST_NAME,
            'team' => $otherTeam,
            'parent_list' => $otherTeam->getLists(),
        ]);

        $this->cantAccessToDeleteTeamList($member, $list);
        $this->cantAccessToDeleteTeamList($superAdmin, $list);
        $this->cantAccessToDeleteTeamList($teamAdmin, $otherList);
        $this->cantAccessToDeleteTeamList($teamAdmin, $team->getDefaultDirectory());
        $this->cantAccessToDeleteTeamList($domainAdmin, $team->getDefaultDirectory());

        $this->canDeleteTeamList($teamAdmin, $list);
        $this->canDeleteTeamList($domainAdmin, $otherList);
    }

    private function canCreateTeamList(User $user, Team $team, string $label)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/lists', $team->getId()->toString()), [
            'label' => $label,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    private function cantAccessToCreateTeamList(User $user, Team $team)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/lists', $team->getId()->toString()), [
            'label' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    private function createListValidate(User $user, Team $team)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendPOST(sprintf('teams/%s/lists', $team->getId()->toString()), [
            'label' => self::DEFAULT_LIST_NAME,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('List with such label already exists');

        $I->sendPOST(sprintf('teams/%s/lists', $team->getId()->toString()), [
            'label' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('This value should not be blank.');
    }

    private function editListValidate(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()), [
            'label' => Directory::LIST_DEFAULT,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('List with such label already exists');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()), [
            'label' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('This value should not be blank.');
    }

    private function canEditTeamList(User $user, Directory $list, string $label)
    {
        $I = $this->tester;

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()), [
            'label' => $label,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/team_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    private function cantAccessToEditTeamList(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()), [
            'label' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    private function canDeleteTeamList(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    private function cantAccessToDeleteTeamList(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendDELETE(sprintf('teams/%s/lists/%s', $list->getTeam()->getId()->toString(), $list->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }
}
