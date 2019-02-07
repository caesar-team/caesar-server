<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\Link;
use App\Model\View\CredentialsList\InviteView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\LinkView;
use App\Model\View\CredentialsList\UpdateView;
use App\Repository\UserRepository;

class ItemViewFactory
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(Item $item): ItemView
    {
        $view = new ItemView();

        $view->id = $item->getId();
        $view->type = $item->getType();
        $view->lastUpdated = $item->getLastUpdated();
        $view->listId = $item->getParentList()->getId()->toString();
        $view->tags = array_map('strval', $item->getTags()->toArray());

        $view->secret = $item->getSecret();
        $view->link = $this->createLinkView($item->getLink());
        $view->invited = $this->getInvitesCollection($item);
        $view->update = $this->getUpdateView($item->getUpdate());
        $view->ownerId = $this->getOwnerId($item);
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

        $user = $this->userRepository->getByItem($ownerItem);

        $invite = new InviteView();
        $invite->id = $ownerItem->getId()->toString();
        $invite->userId = $user->getId()->toString();
        $invite->lastUpdated = $ownerItem->getLastUpdated();
        $invite->email = $user->getEmail();
        $invite->access = $ownerItem->getAccess();

        $inviteViewCollection[] = $invite;

        return $inviteViewCollection;
    }

    private function getOwnerId(Item $item): string
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        return $this->userRepository->getByItem($ownerItem)->getId()->toString();
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

    private function createLinkView(?Link $link): ?LinkView
    {
        if (null === $link) {
            return null;
        }

        $view = new LinkView();

        $view->id = $link->getId();
        $view->data = $link->getData();
        $view->publicKey = $link->getGuestUser()->getPublicKey();

        return $view;
    }
}
