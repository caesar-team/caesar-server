<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Billing\Audit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class AuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Audit::class);
    }

    /**
     * @return Audit|null
     */
    public function findOneLatest(): ?Audit
    {
        return $this->findOneBy([],['createdAt' => 'DESC']);
    }
}