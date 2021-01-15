<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Event\Item\ItemsDateRefreshEvent;
use App\Model\DTO\Share;
use App\Model\Query\ItemListQuery;
use App\Model\Query\ItemsAllQuery;
use App\Request\Item\KeypairFilterRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    private EventDispatcherInterface $dispatcher;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, Item::class);
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string[] $itemIds
     *
     * @return string[]
     */
    public function getDiffItems(array $itemIds, ?string $teamId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->select('item.id')
            ->where('item.id IN (:items)')
            ->setParameter('items', $itemIds)
        ;

        if (null !== $teamId && Uuid::isValid($teamId)) {
            $queryBuilder
                ->andWhere('item.team = :team')
                ->setParameter('team', $teamId)
            ;
        }

        $existItems = $queryBuilder->getQuery()->getScalarResult();
        $existItems = array_column($existItems, 'id');

        return array_values(array_diff($itemIds, $existItems));
    }

    /**
     * @return Item[]
     */
    public function getAllUserItems(ItemsAllQuery $query): array
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->where('item.owner = :user OR item.team IN (:teams)')
            ->setParameter('user', $query->getUser())
            ->setParameter('teams', $query->getUser()->getTeamsIds())
            ->orderBy('item.lastUpdated', 'ASC')
        ;

        if (null !== $query->getLastUpdated()) {
            $queryBuilder
                ->andWhere('item.lastUpdated >= :lastUpdated')
                ->setParameter('lastUpdated', $query->getLastUpdated());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getKeypairsByRequest(KeypairFilterRequest $request): array
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->where('item.type = :type')
            ->andWhere('item.owner = :user OR (item.team IN (:teams) AND item.relatedItem IS NOT NULL)')
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
            ->setParameter('user', $request->getUser())
            ->setParameter('teams', $request->getUser()->getTeamsIds())
        ;

        if ($request->hasPersonalType()) {
            $queryBuilder->andWhere('item.team IS NULL');
        }
        if ($request->hasTeamType()) {
            $queryBuilder->andWhere('item.team IS NOT NULL');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getKeypairsUserTeam(Team $team, User $user): ?Item
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->where('item.type = :type')
            ->andWhere('item.owner = :user')
            ->andWhere('item.team = :team')
            ->andWhere('item.relatedItem IS NULL')
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
            ->setParameter('user', $user)
            ->setParameter('team', $team)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getByQuery(ItemListQuery $query): array
    {
        $qb = $this->createQueryBuilder('item');

        return $qb
            ->join('item.parentList', 'parentList')
            ->where($qb->expr()->eq('parentList', ':list'))
            ->andWhere('item.status =:status')
            ->setParameter('list', $query->list)
            ->setParameter('status', Item::STATUS_FINISHED)
            ->orderBy('item.lastUpdated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Item[]
     */
    public function getFavoritesItems(User $user, ?Team $team = null): iterable
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->innerJoin('item.parentList', 'list')
        ;

        if (null === $team) {
            $queryBuilder
                ->innerJoin(User::class, 'user', Join::WITH, 'user.lists = list OR user.inbox = list OR user.trash = list OR user = item.owner')
                ->where('user.id = :user')
                ->andWhere('item.team IS NULL')
                ->andWhere('item.favorite = true')
                ->setParameter('user', $user->getId())
            ;
        } else {
            $queryBuilder
                ->andWhere('item.team = :team')
                ->andWhere('item.teamFavorite LIKE :user_like')
                ->setParameter('user_like', '%'.$user->getId()->toString().'%')
                ->setParameter('team', $team)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function getTeamKeyPairByUser(User $user, Team $team): ?Item
    {
        $queryBuilder = $this
            ->createQueryBuilder('item')
            ->where('item.team = :team')
            ->andWhere('item.owner = :user')
            ->andWhere('item.type = :type')
            ->andWhere('item.relatedItem IS NULL')
            ->setParameter('team', $team)
            ->setParameter('user', $user)
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function getPersonalKeyPairByUser(User $user, Item $relatedItem): ?Item
    {
        $queryBuilder = $this
            ->createQueryBuilder('item')
            ->where('item.team IS NULL')
            ->andWhere('item.owner = :user')
            ->andWhere('item.type = :type')
            ->andWhere('item.relatedItem = :item')
            ->setParameter('user', $user)
            ->setParameter('item', $relatedItem)
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function save(Item $item): Item
    {
        $this->_em->persist($item);
        $this->_em->flush();

        $this->dispatcher->dispatch(new ItemsDateRefreshEvent(...[$item]));

        return $item;
    }

    public function remove(Item $item): void
    {
        $this->_em->remove($item);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function resetOwnerTeamItems(Team $team, User $oldOwner, User $newOwner): void
    {
        $queryBuilder = $this->createQueryBuilder('item');

        $queryBuilder
            ->update()
            ->set('item.owner', ':owner')
            ->where('item.owner = :user')
            ->andWhere('item.team = :team')
            ->andWhere('item.originalItem IS NULL')
            ->setParameter('user', $oldOwner)
            ->setParameter('team', $team)
            ->setParameter('owner', $newOwner)
        ;

        $queryBuilder->getQuery()->execute();
    }

    public function getCountOriginalItems(): int
    {
        $queryBuilder = $this->createQueryBuilder('item');
        $queryBuilder
            ->select('COUNT(1)')
            ->where('item.type != :type')
            ->setParameter('type', NodeEnumType::TYPE_KEYPAIR)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Share[]
     */
    public function saveShares(Share ...$shares): array
    {
        $result = [];
        $items = [];
        foreach ($shares as $share) {
            try {
                $this->getEntityManager()->persist($share->getKeypair());
                $this->getEntityManager()->flush();

                $items[] = $share->getKeypair();
                $result[] = $share;
            } catch (\Exception $exception) {
                $this->logger->critical(sprintf('Could not save share, reason: %s', $exception->getMessage()));
            }
        }

        $this->dispatcher->dispatch(new ItemsDateRefreshEvent(...$items));

        return $result;
    }
}
