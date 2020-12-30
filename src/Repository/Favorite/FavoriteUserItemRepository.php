<?php

declare(strict_types=1);

namespace App\Repository\Favorite;

use App\Entity\FavoriteUserItem;
use App\Entity\Item;
use App\Entity\User;
use App\Favorite\Repository\FavoriteUserItemRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FavoriteUserItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method FavoriteUserItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method FavoriteUserItem[]    findAll()
 * @method FavoriteUserItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavoriteUserItemRepository extends ServiceEntityRepository implements FavoriteUserItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FavoriteUserItem::class);
    }

    public function findFavorite(User $user, Item $item): ?FavoriteUserItem
    {
        $queryBuilder = $this->createQueryBuilder('favorite');
        $queryBuilder
            ->where('favorite.user = :user')
            ->andWhere('favorite.item = :item')
            ->setParameter('user', $user)
            ->setParameter('item', $item)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function save(FavoriteUserItem $favorite): void
    {
        $this->getEntityManager()->persist($favorite);
        $this->getEntityManager()->flush();
    }

    public function delete(FavoriteUserItem $favorite): void
    {
        $this->getEntityManager()->remove($favorite);
        $this->getEntityManager()->flush();
    }
}
