<?php

namespace App\Tests\User;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class PermissionTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function listPermission()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var Directory $directory */
        $directory = $I->have(Directory::class, [
            'parent_list' => $user->getLists(),
        ]);
        $I->login($user);

        $I->sendGET('/list');
        $I->seeResponseCodeIs(HttpCode::OK);

        [$inbox] = $I->grabDataFromResponseByJsonPath(sprintf('$[?(@.type=="%s")]', Directory::LIST_INBOX));
        self::assertTrue(!isset($inbox['_links']));

        [$trash] = $I->grabDataFromResponseByJsonPath(sprintf('$[?(@.type=="%s")]', Directory::LIST_TRASH));
        self::assertTrue(!isset($trash['_links']));

        $I->seeResponseByJsonPathContainsJson(sprintf('$[?(@.label=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'sort_list' => [],
            'create_item' => [],
        ]]);
        $I->dontSeeResponseByJsonPathContainsJson(sprintf('$[?(@.label=="%s")]', Directory::LIST_DEFAULT), ['_links' => [
            'delete_list' => [],
            'edit_list' => [],
        ]]);

        [$custom] = $I->grabDataFromResponseByJsonPath(sprintf('$[?(@.label=="%s")]', $directory->getLabel()));
        self::assertThat(json_encode($custom), new JsonContains(['_links' => [
            'sort_list' => [],
            'create_item' => [],
            'delete_list' => [],
            'edit_list' => [],
        ]]));
    }

    /** @test */
    public function selfPermissions()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, ['roles' => [User::ROLE_SUPER_ADMIN]]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, ['roles' => [User::ROLE_ADMIN]]);
        /** @var User $manager */
        $manager = $I->have(User::class, ['roles' => [User::ROLE_MANAGER]]);
        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $guest */
        $guest = $I->have(User::class, ['roles' => [User::ROLE_ANONYMOUS_USER]]);

        $this->canFullUserAccess($manager);
        $this->canFullUserAccess($domainAdmin);

        $this->canPartUserAccess($superAdmin);
        $this->canPartUserAccess($user);

        $this->dontUserAccess($guest);
    }

    /** @test */
    public function itemPermissions()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendPOST('items', [
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'title' => 'item title',
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        [$itemId] = $I->grabDataFromResponseByJsonPath('$.id');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['_links' => [
            'edit_item' => [],
            'move_item' => [],
            'delete_item' => [],
            'favorite_item_toggle' => [],
        ]]);

        $I->sendGET(sprintf('items/%s', $itemId));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['_links' => [
            'edit_item' => [],
            'move_item' => [],
            'delete_item' => [],
            'favorite_item_toggle' => [],
        ]]);
    }

    private function canFullUserAccess(User $user): void
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['_links' => [
            'create_list' => [],
            'team_create' => [],
        ]]);
    }

    private function canPartUserAccess(User $user): void
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['_links' => [
            'create_list' => [],
        ]]);
        $I->dontSeeResponseContainsJson(['_links' => [
            'team_create' => [],
        ]]);
    }

    private function dontUserAccess(User $user): void
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseContainsJson(['_links' => [
            'create_list' => [],
            'team_create' => [],
        ]]);
    }
}
