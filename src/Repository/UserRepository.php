<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Query\UserQuery;
use App\Model\Response\PaginatedList;
use App\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    use PaginatorTrait;

    private DirectoryRepository $directoryRepository;

    public function __construct(ManagerRegistry $registry, DirectoryRepository $directoryRepository)
    {
        parent::__construct($registry, User::class);

        $this->directoryRepository = $directoryRepository;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByItem(Item $item): ?User
    {
        $list = $item->getParentList();

        return $this->getByList($list);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByList(Directory $list): ?User
    {
        $parent = $list->getParentList();
        if (null !== $parent) {
            return $this->getByList($parent);
        }

        return $this->directoryRepository->getUserByList($list);
    }

    public function getByQuery(UserQuery $query): PaginatedList
    {
        $teams = [];
        foreach ($query->getUserTeams() as $userTeam) {
            $teams[] = $userTeam->getTeam()->getId();
        }
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->join('user.userTeams', 'userTeams')
            ->where($queryBuilder->expr()->neq('user', ':userId'))
            ->andWhere('userTeams.team IN(:teams)')
            ->andWhere($queryBuilder->expr()->isNotNull('user.publicKey'))
            ->setParameter('teams', $teams)
            ->setParameter('userId', $query->getUser())
            ->setMaxResults($query->getPerPage())
            ->setFirstResult($query->getFirstResult());

        if ($query->name) {
            $queryBuilder
                ->andWhere('LOWER(user.username) LIKE :username')
                ->setParameter('username', '%'.mb_strtolower($query->name).'%');
        }

        return $this->createPaginatedList($queryBuilder, $query);
    }

    /**
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneWithPublicKeyByEmail(string $email): ?User
    {
        $qb = $this->createQueryBuilder('user');

        return $qb
            ->where($qb->expr()->eq('user.email', ':email'))
            ->andWhere($qb->expr()->isNotNull('user.publicKey'))
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByEmail(string $email): ?User
    {
        $queryBuilder = $this->createQueryBuilder('user');

        return $queryBuilder
            ->where('LOWER(user.email) = :email')
            ->setParameter('email', mb_strtolower($email))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array|User[]
     */
    public function findByTeam(Team $team): array
    {
        $qb = $this->createQueryBuilder('user');
        $qb->innerJoin('user.userTeams', 'userTeams');
        $qb->where('userTeams.team =:team');
        $qb->setParameter('team', $team);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|User[]
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->createQueryBuilder('user');
        $qb->where('user.id IN(:ids)');
        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|User[]
     */
    public function findByPartOfEmail(string $partOfEmail): array
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->where('LOWER(user.email) LIKE :email')
            ->andWhere('LOWER(user.roles) NOT LIKE :role')
            ->setParameter('email', '%'.mb_strtolower($partOfEmail).'%')
            ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllExceptAnonymous(): array
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->andWhere('LOWER(user.roles) NOT LIKE :role')
            ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
