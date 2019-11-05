<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ItemUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class ItemUpdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemUpdate::class);
    }

    public function persist(ItemUpdate $update): void
    {
        $this->_em->persist($update);
    }
}