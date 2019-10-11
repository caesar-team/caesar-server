<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Billing\Plan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class PlanRepository
 * @method Plan|null findOneByActive(bool $isActive)
 */
final class PlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }

    /**
     * @param Plan $newPlan
     * @throws ORMException
     */
    public function persist(Plan $newPlan)
    {
        $this->_em->persist($newPlan);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush()
    {
        $this->_em->flush();
    }

    /**
     * @param Plan $plan
     * @throws ORMException
     */
    public function remove(Plan $plan)
    {
        $this->_em->remove($plan);
    }
}