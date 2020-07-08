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
            'string' => 'fingerprint',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $I->login($user);
        $I->setCookie('fingerprint', 'fingerprint');
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['twoFactorAuthState' => SecurityBootstrapView::STATE_SKIP]);

        $I->updateInDatabase('fos_user', [
            'google_authenticator_secret' => null,
        ], ['id' => $user->getId()->toString()]);

        $I->setCookie('fingerprint', 'fingerprint');
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['twoFactorAuthState' => SecurityBootstrapView::STATE_CREATE]);
    }
}
