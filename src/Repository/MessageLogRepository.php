<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MessageLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MessageLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageLog[]    findAll()
 * @method MessageLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageLog::class);
    }

    public function save(MessageLog $log)
    {
        $this->getEntityManager()->persist($log);
        /** @psalm-suppress TooManyArguments */
        $this->getEntityManager()->flush($log);
    }

    public function getLatestDeferredMessages(): array
    {
        $queryBuilder = $this->createQueryBuilder('message');
        $queryBuilder
            ->where('message.deferred = true')
            ->andWhere('message.sentAt IS NULL')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param MessageLog[] $messages
     */
    public function markAsSentMessages(array $messages): void
    {
        $queryBuilder = $this->createQueryBuilder('message');
        $queryBuilder
            ->update()
            ->set('message.sentAt', $queryBuilder->expr()->literal(date('Y-m-d H:i:s')))
            ->where('message.id IN (:messages)')
            ->setParameter('messages', $messages)
        ;

        $queryBuilder->getQuery()->execute();
    }
}
