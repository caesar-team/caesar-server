<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Model\View\Item\InviteItemView;

class InviteItemViewFactory
{
    public function createSingle(Item $item): InviteItemView
    {
        $owner = $item->getSignedOwner();

        $view = new InviteItemView();
        $view->setId($item->getId()->toString());
        $view->setUserId($owner->getId()->toString());
        $view->setUserName($owner->getUsername());
        $view->setUserAvatar($owner->getAvatarLink());
        $view->setUserEmail($owner->getEmail());
        $view->setUserDomainRoles($owner->getRoles());
        $view->setAccess($item->getAccess() ?? AccessEnumType::TYPE_READ);

        return $view;
    }

    /**
     * @param Item[] $items
     *
     * @return InviteItemView[]
     */
    public function createCollection(array $items): array
    {
        return array_values(array_map([$this, 'createSingle'], $items));
    }
}
