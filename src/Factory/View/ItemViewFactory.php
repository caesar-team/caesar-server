<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\Share;
use App\Entity\User;
use App\Model\View\CredentialsList\InviteView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\ShareView;
use App\Model\View\CredentialsList\UpdateView;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;

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

        return $view;
    }

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
     * @param Item $item
     * @return array|ShareView
     */
    private function getSharesCollection(Item $item): array
    {
        $shares = [];
        foreach ($item->getExternalSharedItems() as $shareItem) {
            $shareView = new ShareView();
            $share = $shareItem->getShare();
            $user = $share->getUser();
            $shareView->userId = $user->getId();
            $shareView->email = $user->getEmail();
            $shareView->roles = $user->getRoles();
            $shareView->id = $share->getId();
            $shareView->link = $share->getLink();
            $shareView->status = $this->getStatus($share);
            $shareView->updatedAt = $share->getUpdatedAt();
            $shareView->createdAt = $share->getCreatedAt();
            $shareView->publicKey = $user->getPublicKey();
            $shareView->setLeft(new \DateTime());
            $shares[] = $shareView;
        }

        return $shares;
    }

    private function getStatus(Share $share): string
    {
        switch (true) {
            case $share->getUser()->getLastLogin():
                $status = Share::STATUS_ACCEPTED;
                break;
            default:
                $status = Share::STATUS_WAITING;
        }
        return $status;
    }
}
