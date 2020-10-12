<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Model\DTO\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTeam[]    findAll()
 * @method UserTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTeam::class);
    }

    /**
     * @return array|UserTeam[]
     */
    public function findMembers(Team $team, array $ids = []): array
    {
        $qb = $this->createQueryBuilder('userTeam');
        $qb->where('userTeam.team =:team');
        $qb->andWhere('userTeam.userRole IN(:members)');
        $qb->setParameter('team', $team);
        $qb->setParameter('members', UserTeam::ROLES);

        if (0 < count($ids)) {
            $qb->andWhere('userTeam.user IN(:ids)');
            $qb->setParameter('ids', $ids);
        }

        return $qb->getQuery()->getResult();
    }

    public function remove(UserTeam $userTeam): void
    {
        $this->_em->remove($userTeam);
        $this->_em->flush();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByUserAndTeam(User $user, Team $team): ?UserTeam
    {
        $qb = $this->createQueryBuilder('userTeam');
        $qb->where('userTeam.user =:user');
        $qb->andWhere('userTeam.team =:team');
        $qb->setParameter('user', $user);
        $qb->setParameter('team', $team);
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function save(UserTeam $userTeam): void
    {
        $this->_em->persist($userTeam);
        $this->_em->flush();
    }

    public function saveMember(Member $member): void
    {
        $this->getEntityManager()->persist($member->getUserTeam());
        $this->getEntityManager()->persist($member->getKeypair());
        $this->getEntityManager()->flush();
    }
}
