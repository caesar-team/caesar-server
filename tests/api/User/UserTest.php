<?php

namespace App\Tests\User;

use App\DBAL\Types\Enum\AccessEnumType;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Tests\ApiTester;
use App\Tests\Helper\Doctrine;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class UserTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory|Doctrine
     */
    protected ApiTester $tester;

    protected function _before()
    {
        $this->tester->mockRabbitMQProducer($this->makeEmpty(Producer::class));
    }

    /** @test */
    public function getSelfInfo()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->sendGET('/users/self');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $I->login($user);
        $I->sendGET('/users/self');
        $I->seeResponseContains($user->getEmail());
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/self_user.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function bootstrap()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/user/security/bootstrap');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/bootstrap.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function getPermissions()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/user/permissions');
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('user/permissions.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);
    }

    /** @test */
    public function removeUser()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class, [
            'roles' => [User::ROLE_ADMIN],
        ]);

        /** @var User $member */
        $member = $I->have(User::class);

        /** @var User $anonymous */
        $anonymous = $I->have(User::class, [
            'roles' => [User::ROLE_ANONYMOUS_USER],
            'flow_status' => User::FLOW_STATUS_INCOMPLETE,
        ]);

        $I->login($user);
        $I->sendPOST('list', [
            'label' => 'Remove list',
        ]);
        [$directoryId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST('items', [
            'listId' => $directoryId,
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$itemId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST('items', [
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'favorite' => false,
            'tags' => ['tag'],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$defaultItemId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST('/items/batch/share', [
            'originalItems' => [
                [
                    'originalItem' => $itemId,
                    'items' => [
                        [
                            'userId' => $member->getId()->toString(),
                            'secret' => 'some secret',
                            'cause' => Item::CAUSE_SHARE,
                            'link' => '',
                            'access' => AccessEnumType::TYPE_READ,
                        ],
                        [
                            'userId' => $anonymous->getId()->toString(),
                            'secret' => 'some secret2',
                            'cause' => Item::CAUSE_SHARE,
                            'link' => '',
                            'access' => AccessEnumType::TYPE_READ,
                        ],
                    ],
                ],
            ],
        ]);
        [$shareItemId] = $I->grabDataFromResponseByJsonPath('$.shares[0].items[0].id');
        [$anonymousShareItemId] = $I->grabDataFromResponseByJsonPath('$.shares[0].items[1].id');

        $I->sendPOST('/teams', [
            'title' => uniqid(),
            'icon' => null,
        ]);
        [$teamId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST(sprintf('teams/%s/lists', $teamId), [
            'label' => uniqid(),
        ]);
        [$listTeamId] = $I->grabDataFromResponseByJsonPath('$.id');

        $I->sendPOST(sprintf('teams/%s/members/%s', $teamId, $member->getId()->toString()), [
            'userRole' => UserTeam::USER_ROLE_MEMBER,
        ]);

        /** @var Team $team */
        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $item = $I->createTeamItem($team, $user);
        /** @var Item $child */
        $child = $item->getSharedItems()->first();

        $I->deleteFromDatabase($user);

        $I->dontSeeInDatabase('item', ['id' => $itemId]);
        $I->dontSeeInDatabase('item', ['id' => $defaultItemId]);
        $I->dontSeeInDatabase('directory', ['id' => $directoryId]);
        $I->dontSeeInDatabase('directory', ['id' => $user->getDefaultDirectory()->getId()->toString()]);
        $I->dontSeeInDatabase('item', ['id' => $anonymousShareItemId]);
        $I->dontSeeInDatabase('item', ['id' => $shareItemId]);
        $I->dontSeeInDatabase('item', ['id' => $child->getId()->toString()]);

        $I->seeInDatabase('groups', ['id' => $teamId]);
        $I->seeInDatabase('directory', ['id' => $listTeamId]);
        $I->seeInDatabase('fos_user', ['id' => $anonymous->getId()->toString()]);
    }
}
