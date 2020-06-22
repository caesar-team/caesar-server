<?php

namespace App\Tests\Services;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\UserTeamRepository;
use App\Services\PermissionManager;
use App\Tests\UnitTester;
use Codeception\Test\Unit;

final class PermissionManagerTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @dataProvider ItemAccessProvider
     */
    public function testGetItemAccessLevel(string $userRole, string $expectedAccessType)
    {
        $user = $this->tester->make(User::class);
        $team = $this->tester->make(Team::class);
        $userTeam = $this->tester->make(UserTeam::class, [
            'user' => $user,
            'team' => $team,
            'user_role' => $userRole,
        ]);

        $item = $this->tester->make(Item::class, [
            'team' => $team,
            'owner' => $user,
            'access' => AccessEnumType::TYPE_READ,
        ]);

        $userTeamRepository = $this->make(UserTeamRepository::class, ['findOneByUserAndTeam' => $userTeam]);
        $permissionManager = new PermissionManager($userTeamRepository);
        $access = $permissionManager->getItemAccessLevel($item);

        $this->assertEquals($expectedAccessType, $access);
    }

    public function ItemAccessProvider()
    {
        return [
            [UserTeam::USER_ROLE_ADMIN, AccessEnumType::TYPE_WRITE],
            [UserTeam::USER_ROLE_MEMBER, AccessEnumType::TYPE_READ],
        ];
    }
}
