<?php

namespace App\Tests\User;

use App\Entity\User;
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

        $user = $I->haveUserWithKeys(['google_authenticator_secret' => 'secret']);

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
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

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
        $I->seeResponseCodeIs(HttpCode::OK);
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
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeInDatabase('fingerprint', ['fingerprint' => $fingerprint]);
    }

    /** @test */
    public function getBackupCodes()
    {
        $I = $this->tester;

        $userWithout2Fa = $I->haveUserWithKeys([
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
        ]);
        $user = $I->haveUserWithKeys([
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
            'google_authenticator_secret' => 'secret',
        ]);
        $activeUser = $I->haveUserWithKeys([
            'flow_status' => User::FLOW_STATUS_FINISHED,
        ]);

        $I->login($user);
        $I->sendGET('auth/2fa/backups');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($userWithout2Fa);
        $I->sendGET('auth/2fa/backups');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($activeUser);
        $I->sendGET('auth/2fa/backups');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    /** @test */
    public function acceptBackupCodes()
    {
        $I = $this->tester;

        $user = $I->haveUserWithKeys([
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
            'google_authenticator_secret' => 'secret',
        ]);

        $I->login($user);

        // try to accept without backup codes
        $I->sendPOST('auth/2fa/backups/accept');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeInDatabase('fos_user', ['email' => $user->getEmail(), 'flow_status' => User::FLOW_STATUS_INCOMPLETE]);

        $I->sendGET('auth/2fa/backups');
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('auth/2fa/backups/accept');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeInDatabase('fos_user', ['email' => $user->getEmail(), 'flow_status' => User::FLOW_STATUS_FINISHED]);
    }

    /** @test */
    public function activate2fa()
    {
        $I = $this->tester;

        $user = $I->haveUserWithKeys([
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
        ]);

        $I->login($user);
        $I->sendPOST('auth/2fa/activate', [
            'secret' => '',
            'fingerprint' => 'fingerprint',
            'authCode' => '11111',
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Invalid two-factor authentication code.');
    }
}
