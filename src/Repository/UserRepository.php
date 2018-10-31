<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\Post;
use App\Entity\User;
use App\Model\Query\UserQuery;
use App\Model\Response\PaginatedList;
use App\Traits\PaginatorTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class UserRepository extends EntityRepository
{
    use PaginatorTrait;

    public function getByPost(Post $post): ?User
    {
        $list = $post->getParentList();

        return $this->getByList($list);
    }

    public function getByList(Directory $list): ?User
    {
        $parent = $list->getParentList();
        if (null !== $parent) {
            return $this->getByList($parent);
        }

        $qb = $this->_em->getRepository(Directory::class)->createQueryBuilder('list');

        return $qb
            ->select('user')
            ->join(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->where($qb->expr()->eq('list', ':list'))
            ->setParameter('list', $list)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByQuery(UserQuery $query): PaginatedList
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->where($qb->expr()->neq('user', ':userId'))
            ->andWhere('user.email LIKE :domain')
            ->setParameter('domain', '%@'.$query->getUser()->getDomain())
            ->setParameter('userId', $query->getUser())
            ->setMaxResults($query->getPerPage())
            ->setFirstResult($query->getFirstResult());

        if ($query->name) {
            $qb
                ->andWhere($qb->expr()->like($qb->expr()->lower('user.username'), ':username'))
                ->setParameter('username', '%'.mb_strtolower($query->name).'%');
        }

        return $this->createPaginatedList($qb, $query);
    }
}
