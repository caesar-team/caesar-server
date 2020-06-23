<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class LoginTest extends Unit
{
    private const DEFAULT_MATCHER = 'a950b198a8a1679e0b470d884970f834f78961b61f4e7e9b11dbc87bd2a6a045';

    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function canLogin()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->sendPOST('/auth/srpp/login', [
            'email' => $user->getEmail(),
            'matcher' => self::DEFAULT_MATCHER,
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/auth_srpp_login.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function invalidLogin()
    {
        $I = $this->tester;
        
        /** @var User $user */
        $user = $I->have(User::class);

        $I->sendPOST('/auth/srpp/login', [
            'email' => 'invalid@email',
            'matcher' => self::DEFAULT_MATCHER,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendPOST('/auth/srpp/login', [
            'email' => $user->getEmail(),
            'matcher' => 'invalid-matcher',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}
