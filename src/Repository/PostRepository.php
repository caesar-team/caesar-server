<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Query\PostListQuery;
use Doctrine\ORM\EntityRepository;

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
}
