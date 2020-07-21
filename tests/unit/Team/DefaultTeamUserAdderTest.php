<?php

namespace App\Tests\Team;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;
use App\Team\DefaultTeamUserAdder;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

final class DefaultTeamUserAdderTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var TeamRepository|MockObject
     */
    private $teamRepository;

    /**
     * @var UserTeamRepository|MockObject
     */
    private $userTeamRepository;

    private DefaultTeamUserAdder $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->teamRepository = $this->createMock(TeamRepository::class);
        $this->userTeamRepository = $this->createMock(UserTeamRepository::class);
        $this->service = new DefaultTeamUserAdder($this->teamRepository, $this->userTeamRepository);
    }

    /** @test */
    public function addUserWithoutDefaultTeam()
    {
        $user = $this->make(User::class);

        $this->teamRepository
            ->expects(self::once())
            ->method('getDefaultTeam')
            ->willReturn(null)
        ;

        $this->userTeamRepository
            ->expects(self::never())
            ->method('save')
        ;

        $this->service->addUser($user);
    }

    /** @test */
    public function addUserWithExistTeam()
    {
        $user = $this->make(User::class, ['getUserTeamByTeam' => $this->make(UserTeam::class)]);
        $team = $this->make(Team::class);

        $this->teamRepository
            ->expects(self::once())
            ->method('getDefaultTeam')
            ->willReturn($team)
        ;

        $this->userTeamRepository
            ->expects(self::never())
            ->method('save')
        ;

        $this->service->addUser($user);
    }

    /** @test */
    public function addUser()
    {
        $user = $this->createMock(User::class);
        $team = $this->make(Team::class);

        $this->teamRepository
            ->expects(self::once())
            ->method('getDefaultTeam')
            ->willReturn($team)
        ;

        $user->expects(self::once())->method('addUserTeam');

        $this->userTeamRepository
            ->expects(self::once())
            ->method('save')
        ;

        $this->service->addUser($user);
    }
}
