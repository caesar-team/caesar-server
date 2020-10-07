<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Model\View\Item\ItemView;
use Symfony\Component\Security\Core\Security;

class ItemViewFactory
{
    private Security $security;

    private InviteItemViewFactory $inviteItemViewFactory;

    private UpdateItemViewFactory $updateItemViewFactory;

    private SharedChildItemViewFactory $sharedChildItemViewFactory;

    public function __construct(
        Security $security,
        InviteItemViewFactory $inviteItemViewFactory,
        UpdateItemViewFactory $updateItemViewFactory,
        SharedChildItemViewFactory $sharedChildItemViewFactory
    ) {
        $this->security = $security;
        $this->inviteItemViewFactory = $inviteItemViewFactory;
        $this->updateItemViewFactory = $updateItemViewFactory;
        $this->sharedChildItemViewFactory = $sharedChildItemViewFactory;
    }

    public function createSingle(Item $item): ItemView
    {
        $view = new ItemView($item);

        $view->setId($item->getId()->toString());
        $view->setType($item->getType());
        $view->setLastUpdated($item->getLastUpdated());
        $view->setListId($item->getParentList()->getId()->toString());
        $view->setPreviousListId($item->getPreviousListId());
        $view->setSecret($item->getSecret());

        $view->setInvited($this->inviteItemViewFactory->createCollection($item->getKeyPairItemsWithoutRoot()));
        $view->setOwnerId($item->getOwner()->getId()->toString());
        if (null === $item->getTeam()) {
            $view->setFavorite($item->isFavorite());
        } else {
            $user = $this->security->getUser();
            if ($user instanceof User) {
                $view->setFavorite($item->isTeamFavorite($user));
            }
        }
        $view->setOriginalItemId($item->getOriginalItemId());
        if ($item->getRelatedItem()) {
            $view->setRelatedItemId($item->getRelatedItem()->getId()->toString());
        }

        $sharedItems = $item->getUniqueOwnerShareItems(Item::CAUSE_SHARE);
        if (!empty($sharedItems) && current($sharedItems) instanceof Item) {
            $sharedItem = current($sharedItems);
            $view->setShared(
                $this->sharedChildItemViewFactory->createSingle($sharedItem)
            );
        }

        if (null !== $item->getUpdate()) {
            $view->setUpdate($this->updateItemViewFactory->createSingle($item->getUpdate()));
        }

        $view->setIsShared($item->hasSystemItems());
        $view->setTeamId($item->getTeamId());

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return ItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_map([$this, 'createSingle'], $items);
    }
}
