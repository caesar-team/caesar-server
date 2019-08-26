<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Model\View\CredentialsList\ChildItemView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\UpdateView;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;
use App\Utils\ChildItemAwareInterface;
use Doctrine\Common\Collections\Collection;

class ItemViewFactory
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Item $item
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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
        $view->owner = $this->getOwner($item);
        $view->favorite = $item->isFavorite();
        $view->sort = $item->getSort();
        $view->originalItemId = $item->getOriginalItem()?$item->getOriginalItem()->getId()->toString():null;

        return $view;
    }

    /**
     * @param array|Item[] $items
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createList(array $items)
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
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createSharedItems(string $id, array $items)
    {
        $view = new ItemView();
        $view->originalItemId = $id;
        $childItems = [];
        foreach ($items as $item) {
            $childItem = new ChildItemView();
            $childItem->id = $item->getId()->toString();
            $childItem->lastUpdated = $item->getLastUpdated()->format('Y-m-d H:i:s');
            $user = $this->userRepository->getByItem($item);
            $childItem->userId = $user->getId()->toString();
            $childItems[] = $childItem;
        }
        $view->items = $childItems;

        return $view;
    }

    /**
     * @param Item $item
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getInvitesCollection(Item $item)
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $children = [];
        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems());
        foreach ($sharedItems as $childItem) {
            $user = $this->userRepository->getByItem($childItem);

            $childItemView = new ChildItemView();
            $childItemView->id = $childItem->getId()->toString();
            $childItemView->userId = $user->getId()->toString();
            $childItemView->email = $user->getEmail();
            $childItemView->lastUpdated = $childItem->getLastUpdated();
            $childItemView->access = $childItem->getAccess();
            $children[] = $childItemView;
        }

        return $children;
    }

    /**
     * @param Item $item
     * @return UserView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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
     * @param \Countable|ChildItemAwareInterface[]|Collection $childItems
     * @param string $cause
     * @return array
     */
    private function extractChildItemByCause(\Countable $childItems, string $cause = Item::CAUSE_INVITE): array
    {
        return $childItems->filter(function(ChildItemAwareInterface $childItem) use ($cause) {
            return $cause === $childItem->getCause();
        })->toArray();
    }

    /**
     * @param Item $item
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getSharesCollection(Item $item)
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

        $user = $this->userRepository->getByItem($item);

        $childItemView = new ChildItemView();
        $childItemView->id = $item->getId()->toString();
        $childItemView->userId = $user->getId()->toString();
        $childItemView->email = $user->getEmail();
        $childItemView->lastUpdated = $item->getLastUpdated();
        $childItemView->access = $item->getAccess();
        $childItemView->link = $item->getLink();
        $childItemView->isAccepted = User::FLOW_STATUS_FINISHED === $user->getFlowStatus();
        $childItemView->publicKey = $user->getPublicKey();
        $shares = $childItemView;

        return $shares;
    }
}
