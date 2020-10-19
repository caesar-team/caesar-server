<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class SrpTest extends Unit
{
    public const PUBLIC_EPHEMERAL_VALUE = 'ae7a03327be09c7ef48acb66e9e20acd8bb595f307615d74f3783a1d4247437d87f351bf17022fce5042bd0cfc94289ac6bbf1637a652b89fd6095656fd7a71e';
    public const SEED = 'e4448a3d14af7a3e211c44802ff9f1181d899eff8cd21fa83fc48cf5794caaf91d5516954372fdadfbe931f6ed85a85d36bd325b576bc52255cd03a26865fb85';

    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function prepareLogin()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $userWithoutSrp = $I->have(User::class, [
            'srp' => null,
        ]);

        $I->sendPOST('/auth/srpp/login_prepare', [
            'email' => $user->getEmail(),
            'publicEphemeralValue' => self::PUBLIC_EPHEMERAL_VALUE,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->assertEquals([self::SEED], $I->grabDataFromResponseByJsonPath('$.seed'));

        $schema = $I->getSchema('security/prepare_srp.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendPOST('/auth/srpp/login_prepare', [
            'email' => 'some-user',
            'publicEphemeralValue' => self::PUBLIC_EPHEMERAL_VALUE,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendPOST('/auth/srpp/login_prepare', [
            'email' => $userWithoutSrp->getEmail(),
            'publicEphemeralValue' => self::PUBLIC_EPHEMERAL_VALUE,
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
