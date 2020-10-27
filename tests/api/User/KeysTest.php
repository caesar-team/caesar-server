<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class KeysTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getPublicKey()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class);
        $user = $I->haveUserWithKeys();

        /** @var User $userWithoutKeys */
        $userWithoutKeys = $I->have(User::class);

        $I->login($admin);
        $I->sendGET(sprintf('/key/%s', $user->getEmail()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($user->getPublicKey());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/public_key.json'));

        $I->sendGET(sprintf('/key/%s', $userWithoutKeys->getEmail()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/public_key.json'));
    }

    /** @test */
    public function getBatchPublicKeys()
    {
        $I = $this->tester;

        /** @var User $admin */
        $admin = $I->have(User::class);
        $user = $I->haveUserWithKeys();
        $otherUser = $I->haveUserWithKeys();

        $I->login($admin);
        $I->sendPOST('/key/batch', [
            'emails' => [$user->getEmail(), $otherUser->getEmail()],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($user->getPublicKey());
        $I->seeResponseContains($otherUser->getPublicKey());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/public_keys.json'));
    }

    /** @test */
    public function getSelfKeys()
    {
        $I = $this->tester;

        $user = $I->haveUserWithKeys();

        $I->login($user);
        $I->sendGET('/keys');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/keys.json'));

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/keys');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function updateSelfKeys()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('/keys', [
            'encryptedPrivateKey' => uniqid(),
            'publicKey' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /** @test */
    public function updateKeys()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $userWithoutKeys */
        $userWithoutKeys = $I->have(User::class);

        $I->login($user);
        $I->sendPOST(sprintf('/keys/%s', $userWithoutKeys->getEmail()), [
            'encryptedPrivateKey' => uniqid(),
            'publicKey' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendPOST(sprintf('/keys/%s', $userWithoutKeys->getEmail()), [
            'encryptedPrivateKey' => uniqid(),
            'publicKey' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
