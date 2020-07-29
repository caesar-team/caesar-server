<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Limiter\Inspector\UserCountInspector;
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

    protected function _before()
    {
        $this->tester->executeQuery('TRUNCATE fos_user CASCADE;');
    }

    /** @test */
    public function limitCreateUser()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->setLimiterSize(UserCountInspector::class, 2);

        $I->login($user);
        $I->sendPOST('/user', $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('/user', $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('/user', $this->getUserRequestBody(['roles' => User::ROLE_USER]));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('/user', $this->getUserRequestBody(['roles' => User::ROLE_USER]));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Maximum number of users is reached. Contact your Administrator');

        $I->setLimiterSize(UserCountInspector::class, -1);
        $I->sendPOST('/user', $this->getUserRequestBody(['roles' => User::ROLE_USER]));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function limitCreateBatchUser()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->setLimiterSize(UserCountInspector::class, 2);

        $I->login($user);
        $I->sendPOST('/user/batch', [
            'users' => [
                $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_USER]),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('/user/batch', [
            'users' => [
                $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_USER]),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Maximum number of users is reached. Contact your Administrator');

        $I->setLimiterSize(UserCountInspector::class, -1);
        $I->sendPOST('/user/batch', [
            'users' => [
                $this->getUserRequestBody(['roles' => User::ROLE_ANONYMOUS_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_USER]),
                $this->getUserRequestBody(['roles' => User::ROLE_USER]),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function getUserRequestBody(array $options = []): array
    {
        return array_merge([
            'email' => sprintf('%s@email.com', uniqid()),
            'plainPassword' => 'qweqwe',
            'encryptedPrivateKey' => 'private',
            'publicKey' => 'public',
            'seed' => '123',
            'verifier' => '456',
            'roles' => User::ROLE_USER,
        ], $options);
    }
}
