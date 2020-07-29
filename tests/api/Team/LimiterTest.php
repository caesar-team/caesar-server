<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Limiter\Inspector\TeamCountInspector;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class LimiterTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function limitCreateTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->setLimiterSize(TeamCountInspector::class, 1);

        $I->login($user);
        $I->sendPOST('/teams', [
            'title' => uniqid(),
            'icon' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPOST('/teams', [
            'title' => uniqid(),
            'icon' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Maximum number of teams is reached. Contact your Administrator');

        $I->setLimiterSize(TeamCountInspector::class, -1);

        $I->sendPOST('/teams', [
            'title' => uniqid(),
            'icon' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
