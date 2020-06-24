<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Model\View\CredentialsList\ChildItemView;
use App\Model\View\CredentialsList\InviteItemView;
use App\Model\View\CredentialsList\UpdateView;
use App\Model\View\Item\ItemView;
use App\Services\PermissionManager;
use App\Utils\ChildItemAwareInterface;
use Doctrine\Common\Collections\Collection;

class ItemViewFactory
{
    private PermissionManager $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
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
        $view->setInvited($this->getInvitesCollection($item));
        $view->setShared($this->getSharesCollection($item));
        $view->setUpdate($this->getUpdateView($item->getUpdate()));
        $view->setOwnerId($item->getOwner()->getId()->toString());
        $view->setFavorite($item->isFavorite());
        $view->setSort($item->getSort());
        $view->setOriginalItemId($item->getOriginalItemId());

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

    //@todo candidate to refactoring
    private function getInvitesCollection(Item $item): array
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $children = [];
        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems());
        foreach ($sharedItems as $childItem) {
            $childItemView = new InviteItemView();
            $childItemView->id = $childItem->getId()->toString();
            $childItemView->userId = $childItem->getSignedOwner()->getId()->toString();
            $childItemView->access = $childItem->getAccess();
            $children[] = $childItemView;
        }

        $collection = [];

        return array_filter($children, function (InviteItemView $inviteItemView) use (&$collection) {
            if (!in_array($inviteItemView->userId, $collection)) {
                $collection[] = $inviteItemView->userId;

                return true;
            }

            return false;
        });
    }

    private function getUpdateView(?ItemUpdate $update): ?UpdateView
    {
        if (null === $update) {
            return null;
        }

        $view = new UpdateView();
        $view->userId = $update->getUpdatedBy()->getId()->toString();
        $view->createdAt = $update->getLastUpdated();
        $view->secret = $update->getSecret();

        return $view;
    }

    /**
     * @param ChildItemAwareInterface[]|Collection $childItems
     *
     * @return array|Item[]
     */
    private function extractChildItemByCause(Collection $childItems, string $cause = Item::CAUSE_INVITE): array
    {
        return $childItems->filter(function (ChildItemAwareInterface $childItem) use ($cause) {
            return $cause === $childItem->getCause();
        })->toArray();
    }

    private function getSharesCollection(Item $item): ?ChildItemView
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems(), Item::CAUSE_SHARE);

        if (0 === count($sharedItems)) {
            return null;
        }
        $item = current($sharedItems);
        if (!$item instanceof Item) {
            return null;
        }

        $user = $item->getSignedOwner();

        $childItemView = new ChildItemView();
        $childItemView->id = $item->getId()->toString();
        $childItemView->userId = $user->getId()->toString();
        $childItemView->email = $user->getEmail();
        $childItemView->lastUpdated = $item->getLastUpdated();
        $childItemView->access = $this->permissionManager->getItemAccessLevel($item);
        $childItemView->link = $item->getLink();
        $childItemView->isAccepted = User::FLOW_STATUS_FINISHED === $user->getFlowStatus();
        $childItemView->publicKey = $user->getPublicKey();
        $shares = $childItemView;

        return $shares;
    }
}
