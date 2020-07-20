<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Directory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Directory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Directory[]    findAll()
 * @method Directory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Directory::class);
    }

    public function getMaxPosition(): int
    {
        $query = $this->createQueryBuilder('directory');
        $query->select('MAX(directory.sort) AS max_position');

        return (int) $query->setMaxResults(1)->getQuery()->getSingleScalarResult();
    }

    public function getUserByList(Directory $list): ?User
    {
        $queryBuilder = $this->createQueryBuilder('list');

        return $queryBuilder
            ->select('user')
            ->join(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list')
            ->where($queryBuilder->expr()->eq('list', ':list'))
            ->setParameter('list', $list)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getMovableListsByUser(User $user): array
    {
        $lists = array_merge($user->getUserPersonalLists(), $this->getTeamListsByUser($user));
        $lists = array_filter($lists, static function (Directory $directory) {
            return NodeEnumType::TYPE_LIST === $directory->getRole();
        });

        return array_values($lists);
    }

    public function getTeamListsByUser(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('list');
        $queryBuilder
            ->select('list')
            ->innerJoin('list.team', 'team')
            ->innerJoin('team.userTeams', 'user_teams')
            ->where('list.team IS NOT NULL')
            ->andWhere('user_teams.user = :user')
            ->andWhere('user_teams.userRole IN (:roles)')
            ->setParameter('user', $user)
            ->setParameter('roles', [UserTeam::USER_ROLE_MEMBER, UserTeam::USER_ROLE_ADMIN])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Directory $directory): void
    {
        $this->_em->persist($directory);
        $this->_em->flush();
    }

    public function remove(Directory $directory): void
    {
        $this->_em->remove($directory);
        $this->_em->flush();
    }
}
