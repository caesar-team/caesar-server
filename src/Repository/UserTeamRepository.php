<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Model\DTO\Member;
use App\Model\Query\MemberListQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

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

    public function deleteById(string $userTeamId)
    {
        $this->getEntityManager()
            ->getConnection()
            ->executeQuery('DELETE FROM user_group WHERE id = :id', ['id' => $userTeamId])
        ;
    }

    /**
     * @return array|UserTeam[]
     */
    public function findMembersByTeam(Team $team): array
    {
        $queryBuilder = $this->getQueryBuilderByTeam($team);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return UserTeam[]
     */
    public function getMembersByQuery(MemberListQuery $query): array
    {
        $queryBuilder = $this->getQueryBuilderByTeam($query->getTeam());

        if (!empty($query->getIds())) {
            $queryBuilder
                ->andWhere('userTeam.user IN(:ids)')
                ->setParameter('ids', $query->getIds())
            ;
        }

        if ($query->isWithoutKeypair()) {
            $queryBuilder
                ->leftJoin(
                    Item::class, 'item', Join::WITH,
                    sprintf('item.team = userTeam.team AND item.owner = userTeam.user AND item.type = \'%s\'', NodeEnumType::TYPE_KEYPAIR)
                )
                ->andWhere('item.id IS NULL')
            ;
        }

        return $queryBuilder->getQuery()->getResult();
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

    private function getQueryBuilderByTeam(Team $team, string $alias = 'userTeam'): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($alias);
        $queryBuilder->where(sprintf('%s.team =:team', $alias))
            ->setParameter('team', $team)
        ;

        return $queryBuilder;
    }
}
