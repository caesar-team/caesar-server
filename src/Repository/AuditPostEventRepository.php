<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Audit\PostEvent;
use App\Entity\User;
use App\Model\Query\AuditEventsQuery;
use App\Model\Response\PaginatedList;
use App\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class AuditPostEventRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostEvent::class);
    }

    public function getEventsByQuery(AuditEventsQuery $query): PaginatedList
    {
        $queryBuilder = $this
            ->createQueryBuilder('event')
            ->innerJoin('event.post', 'post')
            ->innerJoin('post.parentList', 'list')
            ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->leftJoin('post.originalPost', 'sharedPosts')
            ->leftJoin('sharedPosts.parentList', 'sharedPostList')
            ->leftJoin(User::class, 'sharedUser', Join::WITH, 'sharedUser.lists = sharedPostList OR sharedUser.inbox = sharedPostList OR sharedUser.trash = sharedPostList')
            ->where('user.id = :user')
            ->orWhere('sharedUser.id = :user')
            ->setParameter('user', $query->getUser())
            ->orderBy('event.createdAt', 'DESC')
            ->setMaxResults($query->getPerPage())
            ->setFirstResult($query->getFirstResult())
        ;

        if (null !== $query->getPost()) {
            $queryBuilder
                ->andWhere('event.post = :post OR post.originalPost = :post')
                ->setParameter('post', $query->getPost())
            ;
        }

        if (AuditEventsQuery::TAB_SHARED === $query->getTab()) {
            $queryBuilder->andWhere('post.originalPost is not null');
        } elseif (AuditEventsQuery::TAB_PERSONAL === $query->getTab()) {
            $queryBuilder->andWhere('post.originalPost is null');
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
