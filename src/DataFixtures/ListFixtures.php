<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Directory;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ListFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public function loadProd(ObjectManager $manager)
    {
        $ipopov = $this->getRef(User::class, 'ipopov');
        $dspiridonov = $this->getRef(User::class, 'dspiridonov');
        $lists = [];

        $lists['ipopovlist'] = new Directory();
        $lists['ipopovlist']->setLabel('Personal');
        $lists['ipopovlist']->setParentList($ipopov->getLists());

        $lists['dspiridonovlist'] = new Directory();
        $lists['dspiridonovlist']->setLabel('Personal');
        $lists['dspiridonovlist']->setParentList($dspiridonov->getLists());

        $this->save($lists, Directory::class);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
