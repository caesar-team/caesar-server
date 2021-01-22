<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Directory\TeamDirectory;
use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TeamDirectory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamDirectory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamDirectory[]    findAll()
 * @method TeamDirectory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamDirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamDirectory::class);
    }

    /**
     * @return TeamDirectory[]
     */
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
            ->andWhere('list.parentDirectory IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('roles', [UserTeam::USER_ROLE_MEMBER, UserTeam::USER_ROLE_ADMIN])
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
