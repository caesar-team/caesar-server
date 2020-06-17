<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MessageHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageHistory[]    findAll()
 * @method MessageHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageHistory::class);
    }
}
