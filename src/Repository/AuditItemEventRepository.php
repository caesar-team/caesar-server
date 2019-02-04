<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audit\ItemEvent;
use App\Entity\User;
use App\Model\Query\AuditEventsQuery;
use App\Model\Response\PaginatedList;
use App\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class AuditItemEventRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemEvent::class);
    }

    public function getEventsByQuery(AuditEventsQuery $query): PaginatedList
    {
        $queryBuilder = $this
            ->createQueryBuilder('event')
            ->innerJoin('event.item', 'item')
            ->innerJoin('item.parentList', 'list')
            ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->leftJoin('item.originalItem', 'sharedItems')
            ->leftJoin('sharedItems.parentList', 'sharedItemList')
            ->leftJoin(User::class, 'sharedUser', Join::WITH, 'sharedUser.lists = sharedItemList OR sharedUser.inbox = sharedItemList OR sharedUser.trash = sharedItemList')
            ->where('user.id = :user')
            ->orWhere('sharedUser.id = :user')
            ->setParameter('user', $query->getUser())
            ->orderBy('event.createdAt', 'DESC')
            ->setMaxResults($query->getPerPage())
            ->setFirstResult($query->getFirstResult())
        ;

        if (null !== $query->getItem()) {
            $queryBuilder
                ->andWhere('event.item = :item OR item.originalItem = :item')
                ->setParameter('item', $query->getItem())
            ;
        }

        if (AuditEventsQuery::TAB_SHARED === $query->getTab()) {
            $queryBuilder->andWhere('item.originalItem is not null');
        } elseif (AuditEventsQuery::TAB_PERSONAL === $query->getTab()) {
            $queryBuilder->andWhere('item.originalItem is null');
        }

        if ($query->getDateFrom() && $query->getDateTo()) {
            $queryBuilder
                ->andWhere('event.createdAt BETWEEN :dateFrom AND :dateTo')
                ->setParameter('dateFrom', $query->getDateFrom())
                ->setParameter('dateTo', $query->getDateTo())
            ;
        } elseif ($query->getDateFrom()) {
            $queryBuilder
                ->andWhere('event.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $query->getDateFrom())
            ;
        } elseif ($query->getDateTo()) {
            $queryBuilder
                ->andWhere('event.createdAt <= :dateFrom')
                ->setParameter('dateTo', $query->getDateTo())
            ;
        }

        return $this->createPaginatedList($queryBuilder, $query);
    }
}
