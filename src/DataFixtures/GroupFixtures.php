<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Team;
use App\Factory\Entity\TeamFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends AbstractFixture implements FixtureInterface
{
    private TeamFactory $teamFactory;

    public function __construct(TeamFactory $teamFactory)
    {
        $this->teamFactory = $teamFactory;
    }

    /**
     * @throws \Exception
     */
    protected function loadProd(ObjectManager $manager)
    {
        $group = $this->manager->getRepository(Team::class)->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);
        if (!$group) {
            $group = $this->teamFactory->create();
            $group->setAlias(Team::DEFAULT_GROUP_ALIAS);
            $group->setTitle(Team::DEFAULT_GROUP_TITLE);

            $this->manager->persist($group);
            $this->manager->flush();
        }
    }
}
