<?php

namespace App\Tests;

use App\Entity\User;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UserTest extends Unit
{
    protected ApiTester $tester;

    public function testLogin()
    {
        /** @var User $user */
        $user = $this->tester->have(User::class);

        $this->tester->sendPOST('/auth/srpp/login', [
            'email' => $user->getEmail(),
            'matcher' => 'a950b198a8a1679e0b470d884970f834f78961b61f4e7e9b11dbc87bd2a6a045',
        ]);

        $this->tester->seeResponseCodeIs(HttpCode::OK);

        $schema = $this->tester->getSchema('auth_srpp_login.json');
        $this->tester->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
