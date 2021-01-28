<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Model\Query\UserListQuery;
use App\Security\Domain\Repository\AllowedDomainRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private DirectoryRepository $directoryRepository;

    private AllowedDomainRepositoryInterface $domainRepository;

    public function __construct(
        ManagerRegistry $registry,
        DirectoryRepository $directoryRepository,
        AllowedDomainRepositoryInterface $domainRepository
    ) {
        parent::__construct($registry, User::class);

        $this->directoryRepository = $directoryRepository;
        $this->domainRepository = $domainRepository;
    }

    public function save(User $user): void
    {
        $this->_em->persist($user);
        /** @psalm-suppress TooManyArguments */
        $this->_em->flush($user);
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
     * @param string[] $emails
     *
     * @return User[]
     */
    public function getUsersWithKeysByEmails(array $emails): array
    {
        $queryBuilder = $this->createQueryBuilder('user');

        return $queryBuilder
            ->where('LOWER(user.email) IN (:emails)')
            ->setParameter('emails', array_map('mb_strtolower', $emails))
            ->getQuery()
            ->getResult()
        ;
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
     * @return User[]
     */
    public function findByIds(array $ids): array
    {
        $ids = array_filter($ids, static function (string $id) {
            return Uuid::isValid($id);
        });

        if (empty($ids)) {
            return [];
        }

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

    public function findAllExceptAnonymous(?string $role = null): array
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->andWhere('LOWER(user.roles) NOT LIKE :role')
            ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
        ;

        if (null !== $role) {
            $queryBuilder
                ->andWhere('LOWER(user.roles) LIKE :filter_role')
                ->setParameter('filter_role', '%'.mb_strtolower($role).'%')
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getCountActiveUsers(): int
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->select('COUNT(1)')
            ->andWhere('LOWER(user.roles) NOT LIKE :role')
            ->andWhere('user.enabled = true')
            ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findWithoutPublicKey(array $criteria): ?User
    {
        $queryBuilder = $this->createQueryBuilder('user');
        $queryBuilder
            ->where('user.publicKey IS NULL')
            ->andWhere('LOWER(user.email) = :email')
            ->setParameter('email', $criteria['email'] ?? '')
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findUsersByQuery(UserListQuery $query): array
    {
        $queryBuilder = $this->createQueryBuilder('user');

        if (empty($query->getIds())) {
            $queryBuilder
                ->andWhere('LOWER(user.roles) NOT LIKE :role')
                ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
            ;
        }

        if (null !== $query->getRole()) {
            $queryBuilder
                ->andWhere('LOWER(user.roles) LIKE :filter_role')
                ->setParameter('filter_role', '%'.mb_strtolower($query->getRole()).'%')
            ;
        }

        if (!empty($query->getIds())) {
            $queryBuilder
                ->andWhere('user.id IN(:ids)')
                ->setParameter('ids', $query->getIds())
            ;
        }

        if ($query->isDomain()) {
            $domainQuery = [];
            foreach ($this->domainRepository->getAllowedDomains() as $domain) {
                $domainQuery[] = $queryBuilder
                    ->expr()
                    ->like('user.email', $queryBuilder->expr()->literal('%@'.$domain))
                ;
            }

            if (!empty($domainQuery)) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->orX(...$domainQuery))
                ;
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
