<?php

namespace App\Tests\User;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
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

    protected function _before()
    {
        $this->tester->executeQuery('TRUNCATE directory CASCADE;');
        $this->tester->executeQuery('TRUNCATE groups CASCADE;');
        $this->tester->executeQuery('TRUNCATE fos_user CASCADE;');
    }

    /** @test */
    public function getList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        $I->login($user);
        $I->sendGET('/users');
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseContains($otherUser->getEmail());
        $I->seeResponseContains($domainAdmin->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/list_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getFilterList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'email' => 'test@example.com',
        ]);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class, [
            'email' => 'test@example.ru',
        ]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $anonymous */
        $anonymous = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
        ]);

        $I->login($user);
        $I->sendGET(sprintf('/users?ids[]=%s', $anonymous->getId()->toString()));
        $I->seeResponseContains($anonymous->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET(sprintf('/users?role=%s', User::ROLE_ADMIN));
        $I->dontSeeResponseContains($user->getEmail());
        $I->dontSeeResponseContains($otherUser->getEmail());
        $I->dontSeeResponseContains($anonymous->getEmail());
        $I->seeResponseContains($domainAdmin->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET(sprintf('/users?is_domain_user=true', User::ROLE_ADMIN));
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseContains($otherUser->getEmail());
        $I->dontSeeResponseContains($anonymous->getEmail());
        $I->dontSeeResponseContains($domainAdmin->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function getFilteredByIdsList()
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
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseContains($otherUser->getEmail());
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
    public function validationCreateList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $otherUser */
        $otherUser = $I->have(User::class);

        $label = uniqid();
        $I->login($user);
        $I->sendPOST('list', [
            'label' => $label,
            'sort' => 0,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPOST('list', [
            'label' => $label,
            'sort' => 0,
        ]);
        $I->seeResponseContains('List with such label already exists');
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->login($otherUser);
        $I->sendPOST('list', [
            'label' => $label,
            'sort' => 0,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST('list', [
            'label' => $label,
            'sort' => 0,
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

    /** @test */
    public function getMovableLists()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->createTeam($user);

        $I->login($user);
        $I->sendGET('/lists/movable');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseContainsJson(['type' => NodeEnumType::TYPE_TRASH]);
        $I->dontSeeResponseContainsJson(['type' => NodeEnumType::TYPE_INBOX]);
        $I->dontSeeResponseContainsJson(['label' => Directory::LIST_TRASH]);
        $I->dontSeeResponseContainsJson(['label' => Directory::LIST_ROOT_LIST]);

        $schema = $I->getSchema('user/short_directory_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function editList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Directory $otherList */
        $otherList = $I->have(Directory::class, [
            'user' => $user,
            'parent_list' => $user->getLists(),
        ]);

        /** @var Directory $list */
        $list = $I->have(Directory::class, [
            'user' => $user,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('list/%s', $list->getId()->toString()), [
            'label' => $list->getLabel(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('list/%s', $list->getId()->toString()), [
            'label' => uniqid(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('list/%s', $list->getId()->toString()), [
            'label' => $otherList->getLabel(),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('List with such label already exists');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('list/%s', $list->getId()->toString()), [
            'label' => null,
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('This value should not be blank.');
    }

    /** @test */
    public function sortList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Directory $list1 */
        $list1 = $I->have(Directory::class, [
            'user' => $user,
            'parent_list' => $user->getLists(),
            'sort' => 0,
        ]);
        /** @var Directory $list2 */
        $list2 = $I->have(Directory::class, [
            'user' => $user,
            'parent_list' => $user->getLists(),
            'sort' => 1,
        ]);
        /** @var Directory $list2 */
        $list3 = $I->have(Directory::class, [
            'user' => $user,
            'parent_list' => $user->getLists(),
            'sort' => 2,
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->login($user);
        $I->sendPATCH(sprintf('list/%s/sort', $list1->getId()->toString()), [
            'sort' => 2,
        ]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->sendGET('list');
        $I->seeResponseContainsJson(['id' => $list1->getId()->toString(), 'sort' => 2]);
        $I->seeResponseContainsJson(['id' => $list2->getId()->toString(), 'sort' => 0]);
        $I->seeResponseContainsJson(['id' => $list3->getId()->toString(), 'sort' => 1]);
    }
}
