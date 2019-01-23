<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Model\View\CredentialsList\InviteView;
use App\Model\View\CredentialsList\ItemView;
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
        $view->invited = $this->getInvitesCollection($item);
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

        $sharesViewCollection = [];
        foreach ($ownerItem->getSharedItems()->toArray() as $item) {
            $user = $this->userRepository->getByItem($item);

            $share = new InviteView();
            $share->userId = $user->getId()->toString();
            $share->access = AccessEnumType::TYPE_READ;

            $sharesViewCollection[] = $share;
        }

        return $sharesViewCollection;
    }

    private function getOwnerId(Item $item): string
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        return $this->userRepository->getByItem($ownerItem)->getId()->toString();
    }
}
