<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message\BufferedMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class BufferedMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BufferedMessage::class);
    }

    public function persist(BufferedMessage $message): void
    {
        $this->_em->persist($message);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    /**
     * @param \DateTime $today
     * @return array|BufferedMessage[]
     */
    public function findAllByDate(\DateTime $today): array
    {
        $qb = $this->createQueryBuilder('buffered_message');
        $qb->where('buffered_message.createdAt >=:date');
        $qb->setParameter('date', $today);

        return $qb->getQuery()->getResult();
    }

    public function remove(BufferedMessage $message)
    {
        $this->_em->remove($message);
    }
}