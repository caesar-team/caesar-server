<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
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
     * @param Group|null $group
     * @param string $role
     * @throws \Exception
     */
    public function addGroupToUser(User $user, string $role = UserGroup::DEFAULT_USER_ROLE, Group $group = null)
    {
        $group = $group ?: $this->findDefaultGroup();
        $userGroup = new UserGroup();
        $userGroup->setGroup($group);
        $userGroup->setUser($user);
        $userGroup->setUserRole($role);
        $this->manager->persist($userGroup);
    }

    private function findDefaultGroup(): Group
    {
        $group = $this->manager->getRepository(Group::class)->findOneBy(['alias' => Group::DEFAULT_GROUP_ALIAS]);

        if (is_null($group)) {
            throw new \LogicException('At least one group must exists');
        }

        return $group;
    }

    public function findUserGroupByAlias(User $user, string $alias): ?UserGroup
    {
        foreach ($user->getUserGroups() as $userGroup) {
            if ($alias === $userGroup->getGroup()->getAlias()) {
                return $userGroup;
            }
        }

        return null;
    }
}