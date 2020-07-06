<?php

namespace App\Tests\Team;

use App\Entity\Directory;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class PermissionTest extends Unit
{
    private const DEFAULT_LIST_NAME = 'New list';

    private const DOMAIN_ADMIN_ACCESS = [
        'team_delete' => [],
        'team_edit' => [],
        'team_members' => [],
        'team_member_add' => [],
        'team_create_list' => [],
        'team_get_lists' => [],
    ];

    private const TEAM_ADMIN_ACCESS = [
        'team_members' => [],
        'team_member_add' => [],
        'team_create_list' => [],
        'team_get_lists' => [],
    ];

    private const MEMBER_ACCESS = [
        'team_members' => [],
        'team_get_lists' => [],
    ];

    private const USER_TEAM_ACCESS = [
        'team_member_edit' => [],
        'team_member_remove' => [],
    ];

    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function teamPermissions()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $guestUser */
        $guestUser = $I->have(User::class);
        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_SUPER_ADMIN],
        ]);

        $team = $I->createTeam($admin);
        $I->addUserToTeam($team, $user);
        $I->addUserToTeam($team, $teamAdmin, UserTeam::USER_ROLE_ADMIN);

        $I->login($admin);
        $I->sendGET(sprintf('/teams/%s', $team->getId()->toString()));
        $I->seeResponseContainsJson(['_links' => self::DOMAIN_ADMIN_ACCESS]);
        $I->seeResponseByJsonPathContainsJson('$.users[0]', [
            '_links' => self::USER_TEAM_ACCESS,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($teamAdmin);
        $I->sendGET(sprintf('/teams/%s', $team->getId()->toString()));
        $I->seeResponseContainsJson(['_links' => self::TEAM_ADMIN_ACCESS]);
        $I->seeResponseByJsonPathContainsJson('$.users[0]', [
            '_links' => self::USER_TEAM_ACCESS,
        ]);
        $I->dontSeeResponseContainsJson(['_links' => array_diff_key(
            self::DOMAIN_ADMIN_ACCESS,
            self::TEAM_ADMIN_ACCESS
        )]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($user);
        $I->sendGET(sprintf('/teams/%s', $team->getId()->toString()));
        $I->seeResponseContainsJson(['_links' => self::MEMBER_ACCESS]);
        $I->dontSeeResponseContainsJson(['_links' => array_diff_key(
            self::DOMAIN_ADMIN_ACCESS,
            self::MEMBER_ACCESS
        )]);
        $I->dontSeeResponseByJsonPathContainsJson('$.users[0]', [
            '_links' => self::USER_TEAM_ACCESS,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($guestUser);
        $I->sendGET('/teams');
        $I->dontSeeResponseContainsJson(['_links' => self::DOMAIN_ADMIN_ACCESS]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($superAdmin);
        $I->sendGET('/teams');
        $I->dontSeeResponseContainsJson(['_links' => self::DOMAIN_ADMIN_ACCESS]);
        $I->dontSeeResponseByJsonPathContainsJson('$[0].users[0]', [
            '_links' => self::USER_TEAM_ACCESS,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function teamListsPermissions()
    {
        $I = $this->tester;

        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);

        $I->have(Directory::class, [
            'label' => Directory::LIST_DEFAULT,
            'team' => $team,
            'parent_list' => $team->getLists(),
        ]);
        $I->have(Directory::class, [
            'label' => self::DEFAULT_LIST_NAME,
            'team' => $team,
            'parent_list' => $team->getLists(),
        ]);

        $this->canAccessToList($domainAdmin, $team);
        $this->canAccessToList($teamAdmin, $team);
        $this->dontAccessToList($member, $team);
    }

    /** @test */
    public function teamItemPermissions()
    {
        $I = $this->tester;

        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $guestUser */
        $member2 = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        $I->addUserToTeam($team, $member2);

        $item = $I->createTeamItem($team, $member);

        $this->canAccessToEditItem($teamAdmin, $team->getDefaultDirectory());
        $this->canAccessToEditItem($member, $team->getDefaultDirectory());
        $this->dontAccessToEditItem($member2, $team->getDefaultDirectory());
    }

    private function canAccessToList(User $user, Team $team)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET(sprintf('/teams/%s/lists', $team->getId()));

        [$trash] = $I->grabDataFromResponseByJsonPath(sprintf('$[?(@.type=="%s")]', Directory::LIST_TRASH));
        self::assertTrue(!isset($trash['_links']));

        $I->seeResponseByJsonPathContainsJson(sprintf('$[?(@.type=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'team_create_item' => [],
        ]]);
        $I->dontSeeResponseByJsonPathContainsJson(sprintf('$[?(@.type=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'team_edit_list' => [],
            'team_delete_list' => [],
            'team_sort_list' => [],
        ]]);
        $I->seeResponseByJsonPathContainsJson(sprintf('$[?(@.label=="%s")]', self::DEFAULT_LIST_NAME), ['_links' => [
            'team_edit_list' => [],
            'team_delete_list' => [],
            'team_sort_list' => [],
            'team_create_item' => [],
        ]]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function dontAccessToList(User $user, Team $team)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET(sprintf('/teams/%s/lists', $team->getId()));

        [$trash] = $I->grabDataFromResponseByJsonPath(sprintf('$[?(@.type=="%s")]', Directory::LIST_TRASH));
        self::assertTrue(!isset($trash['_links']));

        $I->seeResponseByJsonPathContainsJson(sprintf('$[?(@.type=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'team_create_item' => [],
        ]]);
        $I->dontSeeResponseByJsonPathContainsJson(sprintf('$[?(@.type=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'team_edit_list' => [],
            'team_delete_list' => [],
            'team_sort_list' => [],
        ]]);

        $I->seeResponseByJsonPathContainsJson(sprintf('$[?(@.label=="%s")]', self::DEFAULT_LIST_NAME), ['_links' => [
            'team_create_item' => [],
        ]]);
        $I->dontSeeResponseByJsonPathContainsJson(sprintf('$[?(@.label=="%s")]', self::DEFAULT_LIST_NAME), ['_links' => [
            'team_edit_list' => [],
            'team_delete_list' => [],
            'team_sort_list' => [],
        ]]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function canAccessToEditItem(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET(sprintf('items?listId=%s', $list->getId()->toString()));
        $I->canSeeResponseContainsJson(['_links' => [
            'team_edit_item' => [],
            'team_delete_item' => [],
            'team_move_item' => [],
            'team_batch_share_item' => [],
        ]]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function dontAccessToEditItem(User $user, Directory $list)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET(sprintf('items?listId=%s', $list->getId()->toString()));
        $I->dontSeeResponseContainsJson(['_links' => [
            'team_edit_item' => [],
            'team_delete_item' => [],
            'team_move_item' => [],
            'team_batch_share_item' => [],
        ]]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
