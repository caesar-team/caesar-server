<?php

namespace App\Tests\User;

use App\Model\View\User\SecurityBootstrapView;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use Ramsey\Uuid\Uuid;

class TwoFATest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function resetTwoFA()
    {
        $I = $this->tester;

        $user = $I->haveUserWithKeys([
            'google_authenticator_secret' => 'secret',
        ]);

        $I->haveInDatabase('fingerprint', [
            'id' => Uuid::uuid4(),
            'user_id' => $user->getId()->toString(),
            'fingerprint' => 'fingerprint',
            'created_at' => date('Y-m-d H:i:s'),
            'expired_at' => (new \DateTimeImmutable('+1 days'))->format('Y-m-d H:i:s'),
        ]);

        $I->login($user);
        $I->haveHttpHeader('x-fingerprint', 'fingerprint');
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['twoFactorAuthState' => SecurityBootstrapView::STATE_SKIP]);

        $I->updateInDatabase('fos_user', [
            'google_authenticator_secret' => null,
        ], ['id' => $user->getId()->toString()]);

        $I->haveHttpHeader('x-fingerprint', 'fingerprint');
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['twoFactorAuthState' => SecurityBootstrapView::STATE_CREATE]);
    }

    /** @test */
    public function logoutTwoFa()
    {
        $I = $this->tester;

        $user = $I->haveUserWithKeys();

        $fingerprint = uniqid();
        $I->haveInDatabase('fingerprint', [
            'id' => Uuid::uuid4(),
            'user_id' => $user->getId()->toString(),
            'fingerprint' => $fingerprint,
            'created_at' => date('Y-m-d H:i:s'),
            'expired_at' => (new \DateTimeImmutable('+1 days'))->format('Y-m-d H:i:s'),
        ]);
        $I->login($user);
        $I->sendPOST('logout');

        $I->dontSeeInDatabase('fingerprint', ['fingerprint' => $fingerprint]);
    }

    /** @test */
    public function saveFingerprint()
    {
        $I = $this->tester;

        $code = '11111';
        $fingerprint = uniqid();

        $user = $I->haveUserWithKeys([
            'google_authenticator_secret' => 'secret',
            'backup_codes' => [$I->get2FAHashCode($code)],
        ]);

        $I->login($user);
        $I->sendPOST('auth/2fa', [
            'authCode' => $code,
            'fingerprint' => $fingerprint,
        ]);

        $I->seeInDatabase('fingerprint', ['fingerprint' => $fingerprint]);
    }

    /** @test */
    public function dontSaveFingerprint()
    {
        $I = $this->tester;

        $code = '11111';
        $fingerprint = uniqid();

        $user = $I->haveUserWithKeys([
            'google_authenticator_secret' => 'secret',
            'backup_codes' => [$I->get2FAHashCode($code)],
        ]);

        $I->login($user);
        $I->sendPOST('auth/2fa', [
            'authCode' => $code,
        ]);

        $I->dontSeeInDatabase('fingerprint', ['fingerprint' => $fingerprint]);
    }
}
