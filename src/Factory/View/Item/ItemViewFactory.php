<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\ItemView;
use App\Services\PermissionManager;

class ItemViewFactory
{
    private PermissionManager $permissionManager;

    private InviteItemViewFactory $inviteItemViewFactory;

    private UpdateItemViewFactory $updateItemViewFactory;

    private SharedChildItemViewFactory $sharedChildItemViewFactory;

    public function __construct(
        PermissionManager $permissionManager,
        InviteItemViewFactory $inviteItemViewFactory,
        UpdateItemViewFactory $updateItemViewFactory,
        SharedChildItemViewFactory $sharedChildItemViewFactory
    ) {
        $this->permissionManager = $permissionManager;
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
        $view->setInvited(
            $this->inviteItemViewFactory->createCollection($item->getOwnerSharedItems())
        );
        $view->setOwnerId($item->getOwner()->getId()->toString());
        $view->setFavorite($item->isFavorite());
        $view->setSort($item->getSort());
        $view->setOriginalItemId($item->getOriginalItemId());

        $sharedItems = $item->getOwnerSharedItems(Item::CAUSE_SHARE);
        if (!empty($sharedItems) && current($sharedItems) instanceof Item) {
            $sharedItem = current($sharedItems);
            $view->setShared(
                $this->sharedChildItemViewFactory->createSingle($sharedItem)
            );
        }

        if (null !== $item->getUpdate()) {
            $view->setUpdate($this->updateItemViewFactory->createSingle($item->getUpdate()));
        }

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
