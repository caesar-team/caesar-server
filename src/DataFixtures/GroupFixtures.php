<?php

declare(strict_types=1);

namespace App\DataFixtures;


use App\Entity\Group;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class GroupFixtures extends AbstractFixture implements FixtureInterface
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    protected function loadProd(ObjectManager $manager)
    {
        $group = $this->manager->getRepository(Group::class)->findOneBy(['alias' => Group::DEFAULT_GROUP_ALIAS]);
        if (!$group) {
            $group = new Group();
            $group->setAlias(Group::DEFAULT_GROUP_ALIAS);
            $group->setTitle(Group::DEFAULT_GROUP_TITLE);

            $this->manager->persist($group);
            $this->manager->flush();
        }
    }
}