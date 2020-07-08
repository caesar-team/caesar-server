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

        $schema = $I->getSchema('user/list_user.json');
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

        $schema = $I->getSchema('user/list_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->login($user);
        $I->sendGET(sprintf('/users?ids[]=%s', 'some-invalid-id'));
        $I->cantSeeResponseContains($user->getEmail());
        $I->cantSeeResponseContains($otherUser->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/list_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function createList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $I->login($user);
        $I->sendPOST('list', [
            'label' => 'New list',
            'sort' => 0,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/directory.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendPOST('list', [
            'label' => 'New list',
        ]);
        $I->seeResponseContains('List with such label already exists');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /** @test */
    public function createListByGuest()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
        ]);

        $I->login($user);
        $I->sendPOST('list', [
            'label' => 'New list',
        ]);
        $I->seeResponseContains('Unavailable request');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}
