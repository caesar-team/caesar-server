<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class PermissionTest extends Unit
{
    private const DOMAIN_ADMIN_ACCESS = [
        'team_delete' => [],
        'team_edit' => [],
        'team_members' => [],
        'team_member_add' => [],
    ];

    private const TEAM_ADMIN_ACCESS = [
        'team_members' => [],
        'team_member_add' => [],
    ];

    private const MEMBER_ACCESS = [
        'team_members' => [],
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
    }
}
