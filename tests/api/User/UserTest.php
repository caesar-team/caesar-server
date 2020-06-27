<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UserTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getSelfInfo()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/self_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function bootstrap()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/bootstrap.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getPermissions()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/user/permissions');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/permissions.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
