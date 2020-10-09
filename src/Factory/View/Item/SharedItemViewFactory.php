<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\Entity\Item;
use App\Model\View\Item\SharedItemView;
use App\Services\PermissionManager;

class SharedItemViewFactory
{
    private PermissionManager $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function createSingle(Item $item): SharedItemView
    {
        $user = $item->getSignedOwner();

        $view = new SharedItemView();
        $view->setId($item->getId()->toString());
        $view->setTeamId($item->getTeamId());
        $view->setUserId($user->getId()->toString());
        $view->setEmail($user->getEmail());
        $view->setLastUpdated($item->getLastUpdated());
        $view->setAccess($this->permissionManager->getItemAccessLevel($item));
        $view->setLink($item->getLink());
        $view->setIsAccepted($user->isAccepted());
        $view->setPublicKey($user->getPublicKey());
        $view->setOriginalItemId($item->getOriginalItemId());

        return $view;
    }
}
