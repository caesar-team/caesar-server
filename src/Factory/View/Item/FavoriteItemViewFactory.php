<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Model\View\Item\FavoriteItemView;
use Symfony\Component\Security\Core\Security;

class FavoriteItemViewFactory
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function createSingle(Item $item): FavoriteItemView
    {
        $view = new FavoriteItemView();
        $view->setListId($item->getParentList()->getId()->toString());

        if (null === $item->getTeam()) {
            $view->setFavorite($item->isFavorite());
        } else {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $view->setFavorite($item->isTeamFavorite($user));
            }
        }

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return FavoriteItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
