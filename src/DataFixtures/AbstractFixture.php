<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture as DoctrineAbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFixture extends DoctrineAbstractFixture implements ORMFixtureInterface, ContainerAwareInterface
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @var string
     */
    protected $environment;

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
        $this->environment = $container->getParameter('kernel.environment');
    }

    protected function loadTest(ObjectManager $manager)
    {
    }

    abstract protected function loadProd(ObjectManager $manager);

    protected function isProd(): bool
    {
        return 'prod' === $this->environment;
    }

    /**
     * @return User|AbstractDirectory|Item|object
     */
    protected function getRef(string $className, string $uniqueName)
    {
        return $this->getReference($className.$uniqueName);
    }

    /**
     * @param mixed $object
     */
    protected function addRef(string $className, string $uniqueName, $object)
    {
        $this->addReference($className.$uniqueName, $object);
    }

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
