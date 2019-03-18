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
use Doctrine\Common\Collections\ArrayCollection;

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
        $view->shared = [];
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

        $inviteViewCollection = [];
        foreach ($ownerItem->getSharedItems() as $item) {
            $user = $this->userRepository->getByItem($item);

            $invite = new InviteView();
            $invite->id = $item->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $item->getLastUpdated();
            $invite->access = $item->getAccess();
            $inviteViewCollection[] = $invite;
        }

        foreach ($this->extractMasksByCause($ownerItem->getItemMasks()) as $mask) {
            $user = $mask->getRecipient();
            $invite = new InviteView();
            $invite->id = $mask->getId()->toString();
            $invite->userId = $user->getId()->toString();
            $invite->email = $user->getEmail();
            $invite->lastUpdated = $ownerItem->getLastUpdated();
            $invite->access = $mask->getAccess();
            $inviteViewCollection[] = $invite;
        }

        return $inviteViewCollection;
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
     * @param \Countable $masks
     * @param string $cause
     * @return array|ItemMask[]
     */
    private function extractMasksByCause(\Countable $masks, string $cause = ItemMask::CAUSE_INVITE): array
    {
        return array_filter($masks->toArray(), function(ItemMask $mask) use ($cause) {
            return $cause === $mask->getCause();
        });
    }
}
