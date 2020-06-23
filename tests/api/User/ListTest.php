<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ListTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/users');
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseContains($otherUser->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getFilteredList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $I->login($user);
        $I->sendGET(sprintf('/users?ids[]=%s', $user->getId()));
        $I->seeResponseContains($user->getEmail());
        $I->cantSeeResponseContains($otherUser->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->login($user);
        $I->sendGET(sprintf('/users?ids[]=%s', 'some-invalid-id'));
        $I->cantSeeResponseContains($user->getEmail());
        $I->cantSeeResponseContains($otherUser->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }
}
