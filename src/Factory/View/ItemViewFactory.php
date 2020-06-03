<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Model\View\CredentialsList\ChildItemView;
use App\Model\View\CredentialsList\InviteItemView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\UpdateView;
use App\Model\View\User\UserView;
use App\Services\PermissionManager;
use App\Utils\ChildItemAwareInterface;
use Countable;
use Doctrine\Common\Collections\Collection;

class ItemViewFactory
{
    private PermissionManager $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function create(Item $item): ItemView
    {
        $view = new ItemView();

        $view->id = $item->getId();
        $view->type = $item->getType();
        $view->lastUpdated = $item->getLastUpdated();
        $view->listId = $item->getParentList()->getId()->toString();
        $view->previousListId = $item->getPreviousList() ? $item->getPreviousList()->getId()->toString() : null;

        $view->secret = $item->getSecret();
        $view->invited = $this->getInvitesCollection($item);
        $view->shared = $this->getSharesCollection($item);
        $view->update = $this->getUpdateView($item->getUpdate());
        $view->ownerId = $item->getOwner()->getId()->toString();
        $view->favorite = $item->isFavorite();
        $view->sort = $item->getSort();
        $view->originalItemId = $item->getOriginalItem() ? $item->getOriginalItem()->getId()->toString() : null;

        return $view;
    }

    /**
     * @param array|Item[] $items
     */
    public function createList(array $items): ItemView
    {
        $view = new ItemView();
        $childItems = [];
        foreach ($items as $item) {
            $childItem = new ChildItemView();
            $childItem->id = $item->getId()->toString();
            $childItem->lastUpdated = $item->getLastUpdated()->format('Y-m-d H:i:s');
            $childItem->userId = $this->getOwner($item)->id;
            $childItems[] = $childItem;
        }
        $view->items = $childItems;

        return $view;
    }

    /**
     * @param array|Item[] $items
     */
    public function createSharedItems(string $id, array $items): ItemView
    {
        $view = new ItemView();
        $view->originalItemId = $id;
        $childItems = [];
        foreach ($items as $item) {
            $childItem = new ChildItemView();
            $childItem->id = $item->getId()->toString();
            $childItem->lastUpdated = $item->getLastUpdated()->format('Y-m-d H:i:s');
            $childItem->userId = $item->getSignedOwner()->getId()->toString();
            $childItem->teamId = $item->getTeam() ? $item->getTeam()->getId()->toString() : null;
            $childItems[] = $childItem;
        }
        $view->items = $childItems;

        return $view;
    }

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
            $childItemView->access = $this->permissionManager->getItemAccessLevel($childItem);
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

    private function getOwner(Item $item): UserView
    {
        $user = $item->getOwner();

        return (new UserViewFactory())->create($user);
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
     * @param Countable|ChildItemAwareInterface[]|Collection $childItems
     *
     * @return array|Item[]
     */
    private function extractChildItemByCause(Countable $childItems, string $cause = Item::CAUSE_INVITE): array
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
