<?php

namespace App\Tests\Team;

use App\Entity\User;
use App\Entity\UserTeam;
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
                'title' => 'Vault team test',
                'icon' => null,
            ],
            'keypair' => [
                'secret' => uniqid(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('team/vault.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendPOST('/vault', [
            'team' => [
                'title' => 'Vault team test',
                'icon' => null,
            ],
            'keypair' => [
                'secret' => uniqid(),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->sendPOST('/vault', [
            'team' => [
                'title' => null,
                'icon' => null,
            ],
            'keypair' => [
                'secret' => null,
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /** @test */
    public function managerCreateTeam()
    {
        $I = $this->tester;

        /** @var User $manager */
        $manager = $I->have(User::class, [
            'roles' => [User::ROLE_MANAGER],
        ]);

        $I->login($manager);
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

        $I->seeResponseByJsonPathContainsJson('$.team', [
            '_links' => [
                'team_edit' => [],
                'team_delete' => [],
            ],
        ]);
    }

    /** @test */
    public function batchItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $member */
        $member = $I->have(User::class);

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
        $I->seeResponseCodeIs(HttpCode::OK);
        [$teamId] = $I->grabDataFromResponseByJsonPath('$.team.id');

        $I->sendPOST(sprintf('teams/%s/members', $teamId), [
            'teamRole' => UserTeam::USER_ROLE_MEMBER,
            'secret' => uniqid(),
            'userId' => $member->getId()->toString(),
        ]);

        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['ownerId' => $user->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.keypairs', ['ownerId' => $member->getId()->toString()]);

        $I->login($member);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.keypairs', ['ownerId' => $member->getId()->toString()]);
        $I->dontSeeResponseByJsonPathContainsJson('$.keypairs', ['ownerId' => $user->getId()->toString()]);
    }
}
