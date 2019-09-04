<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Model\Query\UserQuery;
use App\Model\Response\PaginatedList;
use App\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class UserRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param Item $item
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByItem(Item $item): ?User
    {
        $list = $item->getParentList();

        return $this->getByList($list);
    }

    /**
     * @param Directory $list
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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
        $groups = [];
        foreach ($query->getUserGroups() as $userGroup) {
            $groups[] = $userGroup->getGroup()->getId();
        }
        $qb = $this->createQueryBuilder('user');
        $qb
            ->join('user.userGroups','userGroups')
            ->where($qb->expr()->neq('user', ':userId'))
            ->andWhere('userGroups.group IN(:groups)')
            ->andWhere($qb->expr()->isNotNull('user.publicKey'))
            ->setParameter('groups', $groups)
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

    /**
     * @param string $token
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByToken(string $token): ?User
    {
        $qb = $this->createQueryBuilder('user');

        return $qb
            ->where($qb->expr()->eq('user.token', ':token'))
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $email
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByEmail(string $email): ?User
    {
        $qb = $this->createQueryBuilder('user');

        return $qb
            ->where($qb->expr()->eq('user.email', ':email'))
            ->andWhere($qb->expr()->isNotNull('user.publicKey'))
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Item $item): Item
    {
        $this->_em->persist($item);
        $this->_em->flush();

        return $item;
    }

    /**
     * @return QueryBuilder
     */
    private function queryAllCompleted()
    {
        $qb = $this->createQueryBuilder('user');
        $qb->where("user.flowStatus =:status");
        $qb->setParameter('status', User::FLOW_STATUS_FINISHED);

        return $qb;
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountCompleted(): int
    {
        $qb = $this->queryAllCompleted();
        $qb->select($qb->expr()->count('user.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
