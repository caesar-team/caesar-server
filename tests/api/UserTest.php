<?php

namespace App\Tests;

use App\Entity\User;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class UserTest extends Unit
{
    /**
     * @var \App\Tests\ApiTester
     */
    protected $tester;

    public function testLogin()
    {
        /** @var User $user */
        $user = $this->tester->have(User::class);

        $this->tester->sendPOST('/auth/srpp/login', [
            'email' => $user->getEmail(),
            'matcher' => 'a950b198a8a1679e0b470d884970f834f78961b61f4e7e9b11dbc87bd2a6a045'
        ]);

        $schema = [
            "properties" => [
                "secondMatcher" => [
                    "type" => "string"
                ],
                "jwt" => [
                    "type" => "string"
                ]
            ],
            "required" => [
                "jwt",
                "secondMatcher"
            ]
        ];

        $this->tester->seeResponseCodeIs(HttpCode::OK);
        $this->tester->seeResponseIsValidOnJsonSchemaString(json_encode($schema));
    }
}