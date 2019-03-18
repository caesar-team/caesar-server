<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\ItemMask;
use App\Entity\ItemUpdate;
use App\Model\View\CredentialsList\InviteView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\UpdateView;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;
use App\Utils\ChildItemAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
        $view->tags = array_map('strval', $item->getTags()->toArray());

        $view->secret = $item->getSecret();
        $view->invited = $this->getInvitesCollection($item);
        $view->shared = $this->getSharesCollection($item);
        $view->update = $this->getUpdateView($item->getUpdate());
        $view->owner = $this->getOwner($item);
        $view->favorite = $item->isFavorite();
        $view->sort = $item->getSort();

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

        $invites = [];
        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems());
        foreach ($sharedItems as $item) {
            $user = $this->userRepository->getByItem($item);

            $invite = new InviteView();
            $invite->id = $item->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $item->getLastUpdated();
            $invite->access = $item->getAccess();
            $invites[] = $invite;
        }

        foreach ($this->extractChildItemByCause($ownerItem->getItemMasks()) as $mask) {
            $user = $mask->getRecipient();
            $invite = new InviteView();
            $invite->id = $mask->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $ownerItem->getLastUpdated();
            $invite->access = $mask->getAccess();
            $invites[] = $invite;
        }

        return $invites;
    }

    /**
     * @param Item $item
     * @return UserView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getOwner(Item $item): UserView
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }
        $user = $this->userRepository->getByItem($ownerItem);

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
     * @return array|ItemMask[]
     */
    private function extractChildItemByCause(\Countable $childItems, string $cause = ItemMask::CAUSE_INVITE): array
    {
        return array_filter($childItems->toArray(), function(ChildItemAwareInterface $childItem) use ($cause) {
            return $cause === $childItem->getCause();
        });
    }

    /**
     * @param Item $item
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getSharesCollection(Item $item)
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $shares = [];
        $sharedItems = $this->extractChildItemByCause($ownerItem->getSharedItems(), ItemMask::CAUSE_SHARE);
        foreach ($sharedItems as $item) {
            $user = $this->userRepository->getByItem($item);

            $invite = new InviteView();
            $invite->id = $item->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $item->getLastUpdated();
            $invite->access = $item->getAccess();
            $shares[] = $invite;
        }

        foreach ($this->extractChildItemByCause($ownerItem->getItemMasks(), ItemMask::CAUSE_SHARE) as $mask) {
            $user = $mask->getRecipient();
            $invite = new InviteView();
            $invite->id = $mask->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $ownerItem->getLastUpdated();
            $invite->access = $mask->getAccess();
            $shares[] = $invite;
        }

        return $shares;
    }
}
