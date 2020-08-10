<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Entity\User;
use App\Factory\View\Item\ItemViewFactory;
use App\Model\View\Item\ItemView;
use Symfony\Component\Security\Core\Security;

class ItemListViewFactory
{
    private ItemViewFactory $secretViewFactory;

    private ?User $currentUser;

    public function __construct(ItemViewFactory $secretViewFactory, Security $security)
    {
        $this->secretViewFactory = $secretViewFactory;

        $user = $security->getUser();
        if ($user instanceof User) {
            $this->currentUser = $user;
        }
    }

    /**
     * @param Item[] $itemCollection
     *
     * @return ItemView[]
     */
    public function create(array $itemCollection): array
    {
        $viewCollection = [];
        foreach ($itemCollection as $item) {
            if (!$item->getSignedOwner()->equals($this->currentUser) && null === $item->getTeam()) {
                continue;
            }

            $viewCollection[] = $this->secretViewFactory->createSingle($item);
        }

        return $viewCollection;
    }
}
