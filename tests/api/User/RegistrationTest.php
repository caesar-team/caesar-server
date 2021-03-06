<?php

namespace App\Tests\User;

use App\Entity\UserTeam;
use App\Tests\ApiTester;
use App\Tests\Helper\Doctrine;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class RegistrationTest extends Unit
{
    private const SEED = 'e4448a3d14af7a3e211c44802ff9f1181d899eff8cd21fa83fc48cf5794caaf91d5516954372fdadfbe931f6ed85a85d36bd325b576bc52255cd03a26865fb85';
    private const VERIFIER = '9d4550e7fab90cb40015dc44fe1fcf499ed7a5462b2fe5d4ed1f48ca8b3d4e6f7ac85789bc212983440a74028ed931f72ff088015b09723328770e72a9694e7f';

    /**
     * @var ApiTester|REST|DataFactory|Doctrine
     */
    protected ApiTester $tester;

    /** @test */
    public function canRegistration()
    {
        $I = $this->tester;
        $team = $I->createDefaultTeam();
        $email = sprintf('%s@example.com', uniqid());

        $I->sendPOST('/auth/srpp/registration', [
            'email' => $email,
            'seed' => self::SEED,
            'verifier' => self::VERIFIER,
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $srpId = $I->grabFromDatabase('fos_user', 'srp_id', ['username' => $email]);

        $I->seeInDatabase('fos_user', ['username' => $email]);
        $I->seeInDatabase('srp', ['id' => $srpId, 'seed' => self::SEED, 'verifier' => self::VERIFIER]);

        $userId = $I->grabFromDatabase('fos_user', 'id', ['username' => $email]);

        $I->dontSeeInDatabase('user_group', ['group_id' => $team->getId()->toString(), 'user_id' => $userId]);
        $I->seeInDatabase('directory', ['label' => 'default', 'user_id' => $userId]);

        $I->sendPOST('/auth/srpp/registration', [
            'email' => $email,
            'seed' => self::SEED,
            'verifier' => self::VERIFIER,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Wrong email');
    }

    /** @test */
    public function canRegistrationDomainAdmin()
    {
        $I = $this->tester;
        $team = $I->createDefaultTeam();
        $email = getenv('DOMAIN_ADMIN_EMAIL');

        $I->sendPOST('/auth/srpp/registration', [
            'email' => $email,
            'seed' => self::SEED,
            'verifier' => self::VERIFIER,
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->seeInDatabase('fos_user', ['username' => $email]);
        $userId = $I->grabFromDatabase('fos_user', 'id', ['username' => $email]);
        $I->seeInDatabase('user_group', [
            'group_id' => $team->getId()->toString(),
            'user_id' => $userId,
            'user_role' => UserTeam::USER_ROLE_ADMIN,
        ]);
        $I->releaseUsername($email);
    }

    /** @test */
    public function cantRegistrationWithRestrictedDomain()
    {
        $I = $this->tester;
        $email = sprintf('%s@restricted.com', uniqid());

        $I->sendPOST('/auth/srpp/registration', [
            'email' => $email,
            'seed' => self::SEED,
            'verifier' => self::VERIFIER,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Domain restricted.com is not allowed.');
    }
}
