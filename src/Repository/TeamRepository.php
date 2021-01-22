<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Model\DTO\Vault;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function save(Team $team): void
    {
        $this->getEntityManager()->persist($team);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array|Team[]
     */
    public function findByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('team');
        $qb->join('team.userTeams', 'userTeams');
        $qb->where('userTeams.user =:user');
        $qb->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function findAllExcept(array $memberships): array
    {
        $qb = $this->createQueryBuilder('team');

        if (0 < count($memberships)) {
            $qb
                ->where('team.id NOT IN (:teams)')
                ->setParameter('teams', $memberships)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getDefaultTeam(): ?Team
    {
        return $this->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);
    }

    public function getCountTeams(): int
    {
        $queryBuilder = $this
            ->createQueryBuilder('team')
            ->select('COUNT(team.id)')
            ->where('team.alias != :alias OR team.alias IS NULL')
            ->setParameter('alias', Team::DEFAULT_GROUP_ALIAS)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function saveVault(Vault $vault): void
    {
        $this->getEntityManager()->persist($vault->getTeam());
        $this->getEntityManager()->persist($vault->getKeypair());
        $this->getEntityManager()->flush();
    }
}
