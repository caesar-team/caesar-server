<?php

namespace App\Tests;

use App\Entity\User;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ApiDocTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getApiDoc()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $I->login($user);
        $I->sendGET('/doc');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
