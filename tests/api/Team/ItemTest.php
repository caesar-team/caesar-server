<?php

namespace App\Tests\Team;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ItemTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createTeamItem()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_SUPER_ADMIN],
        ]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $guestUser */
        $guestUser = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);

        $directory = $team->getDefaultDirectory();

        $this->canCreateTeamItem($domainAdmin, $directory);
        $this->canCreateTeamItem($teamAdmin, $directory);
        $this->canCreateTeamItem($member, $directory);
        $this->dontCreateTeamItem($superAdmin, $directory);
        $this->dontCreateTeamItem($guestUser, $directory);
    }

    /** @test */
    public function getTeamItems()
    {
        $I = $this->tester;

        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $guestUser */
        $member2 = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        $I->addUserToTeam($team, $member2);

        $item = $I->createTeamItem($team, $member);

        $I->login($teamAdmin);
        $I->sendGET(sprintf('items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->canSeeResponseContainsJson([
            'id' => $item->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($member2);
        $I->sendGET(sprintf('items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->canSeeResponseContainsJson([
            'id' => $item->getId()->toString(),
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function editTeamItem()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_SUPER_ADMIN],
        ]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $guestUser */
        $member2 = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        $I->addUserToTeam($team, $member2);

        $item = $I->createTeamItem($team, $member);

        $this->dontEditTeamItem($superAdmin, $item);
        $this->dontEditTeamItem($member2, $item);

        $this->canEditTeamItem($domainAdmin, $item);
        $this->canEditTeamItem($teamAdmin, $item);
        $this->canEditTeamItem($member, $item);
    }

    /** @test */
    public function deleteTeamItem()
    {
        $I = $this->tester;

        /** @var User $superAdmin */
        $superAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_SUPER_ADMIN],
        ]);
        /** @var User $domainAdmin */
        $domainAdmin = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);
        /** @var User $teamAdmin */
        $teamAdmin = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $guestUser */
        $member2 = $I->have(User::class);

        /** @var Team $team */
        $team = $I->createTeam($teamAdmin);
        $I->addUserToTeam($team, $member);
        $I->addUserToTeam($team, $member2);

        $item = $I->createTeamItem($team, $member);

        $I->login($member);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()->toString()), [
            'listId' => $team->getTrash()->getId()->toString(),
        ]);

        $this->dontDeleteTeamItem($superAdmin, $item);

        $this->canDeleteTeamItem($teamAdmin, $item);
        $item = $I->createTeamItem($team, $member);

        $I->login($member);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()->toString()), [
            'listId' => $team->getTrash()->getId()->toString(),
        ]);

        $this->canDeleteTeamItem($domainAdmin, $item);

        $item = $I->createTeamItem($team, $member);

        $I->login($member);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('/items/%s/move', $item->getId()->toString()), [
            'listId' => $team->getTrash()->getId()->toString(),
        ]);
        $this->canDeleteTeamItem($member, $item);
    }

    private function canDeleteTeamItem(User $user, Item $item)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendDELETE(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    private function dontDeleteTeamItem(User $user, Item $item)
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendDELETE(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    private function canEditTeamItem(User $user, Item $item)
    {
        $I = $this->tester;

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'item' => [
                'secret' => 'secret-edit',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/edit_item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    private function dontEditTeamItem(User $user, Item $item)
    {
        $I = $this->tester;

        $I->login($user);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'item' => [
                'secret' => 'secret-edit',
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }

    private function canCreateTeamItem(User $user, Directory $directory): void
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendPOST('items', [
            'listId' => $directory->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/create_item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    private function dontCreateTeamItem(User $user, Directory $directory): void
    {
        $I = $this->tester;

        $I->login($user);
        $I->sendPOST('items', [
            'listId' => $directory->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $this->assertEquals([403], $I->grabDataFromResponseByJsonPath('$.error.code'));
    }
}
