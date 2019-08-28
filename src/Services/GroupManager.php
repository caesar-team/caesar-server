<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\ORM\EntityManagerInterface;

class GroupManager
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param User $user
     * @param Team|null $group
     * @param string $role
     * @throws \Exception
     */
    public function addGroupToUser(User $user, string $role = UserTeam::DEFAULT_USER_ROLE, Team $group = null)
    {
        $group = $group ?: $this->findDefaultGroup();
        $userGroup = new UserTeam();
        $userGroup->setTeam($group);
        $userGroup->setUser($user);
        $userGroup->setUserRole($role);
        $this->manager->persist($userGroup);
    }

    private function findDefaultGroup(): Team
    {
        $group = $this->manager->getRepository(Team::class)->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);

        if (is_null($group)) {
            throw new \LogicException('At least one group must exists');
        }

        return $group;
    }

    public function findUserGroupByAlias(User $user, string $alias): ?UserTeam
    {
        foreach ($user->getUserGroups() as $userGroup) {
            if ($alias === $userGroup->getTeam()->getAlias()) {
                return $userGroup;
            }
        }

        return null;
    }
}