<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\ShareView;
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
        $view->shared = $this->getSharedCollection($item);
        $view->favorite = $item->isFavorite();

        return $view;
    }

    protected function getSharedCollection(Item $item)
    {
        $ownerItem = $item;
        if (null !== $item->getOriginalItem()) {
            $ownerItem = $item->getOriginalItem();
        }

        $sharesViewCollection = [];
        $allItems = $ownerItem->getSharedItems()->toArray();
        $allItems[] = $ownerItem;
        foreach ($allItems as $item) {
            $user = $this->userRepository->getByItem($item);

            $share = new ShareView();
            $share->userId = $user->getId()->toString();
            $share->owner = $ownerItem === $item;

            $sharesViewCollection[] = $share;
        }

        return $sharesViewCollection;
    }
}
