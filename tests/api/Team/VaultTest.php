<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class VaultTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('/vault', [
            'team' => [
                'title' => uniqid(),
                'icon' => null,
            ],
            'keypair' => [
                'secret' => uniqid(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));

        /** @var User $admin */
        $admin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->login($admin);
        $I->sendPOST('/vault', [
            'team' => [
                'title' => uniqid(),
                'icon' => null,
            ],
            'keypair' => [
                'secret' => uniqid(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/vault.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
