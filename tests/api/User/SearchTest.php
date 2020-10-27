<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class SearchTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function canSearch()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET(sprintf('/users/search?email=%s', $user->getEmail()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('user/search.json'));
    }

    /** @test */
    public function nodFound()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET(sprintf('/users/search?email=%s', 'not-found@email'));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->cantSeeResponseContains('not-found@email');
    }
}
