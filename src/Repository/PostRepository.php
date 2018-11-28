<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Model\Query\PostListQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class PostRepository extends EntityRepository
{
    public function getByQuery(PostListQuery $query): array
    {
        $qb = $this->createQueryBuilder('post');

        return $qb
            ->join('post.parentList', 'parentList')
            ->where($qb->expr()->eq('parentList', ':list'))
            ->setParameter('list', $query->list)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     *
     * @return Post[]|iterable
     */
    public function getFavoritesPosts(User $user): iterable
    {
        $queryBuilder = $this->createQueryBuilder('post');
        $queryBuilder
            ->innerJoin('post.parentList', 'list')
            ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->where('user.id = :user')
            ->andWhere('post.favorite = true')
            ->setParameter('user', $user->getId())
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
