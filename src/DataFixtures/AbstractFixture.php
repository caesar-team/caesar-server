<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Directory;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture as DoctrineAbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFixture extends DoctrineAbstractFixture implements ORMFixtureInterface, ContainerAwareInterface
{
    /** @var ObjectManager */
    protected $manager;

    protected $environment;

    /**
     * @param ObjectManager $manager
     */
    final public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->loadProd($manager);
        if (!$this->isProd()) {
            $this->loadTest($manager);
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->environment = $container->getParameter('environment');
    }

    protected function loadTest(ObjectManager $manager)
    {
    }

    abstract protected function loadProd(ObjectManager $manager);

    /**
     * @return bool
     */
    protected function isProd(): bool
    {
        return 'prod' === $this->environment;
    }

    /**
     * @param string $className
     * @param string $uniqueName
     *
     * @return User|Directory|Post|object
     */
    protected function getRef(string $className, string $uniqueName)
    {
        return $this->getReference($className.$uniqueName);
    }

    /**
     * @param string $className
     * @param string $uniqueName
     * @param        $object
     */
    protected function addRef(string $className, string $uniqueName, $object)
    {
        $this->addReference($className.$uniqueName, $object);
    }

    /**
     * @param array       $collection
     * @param string|null $referenceClass
     */
    protected function save(array $collection, string $referenceClass = null)
    {
        foreach ($collection as $name => $item) {
            if (null !== $referenceClass) {
                $this->addRef($referenceClass, $name, $item);
            }
            $this->manager->persist($item);
        }

        $this->manager->flush();
    }
}
