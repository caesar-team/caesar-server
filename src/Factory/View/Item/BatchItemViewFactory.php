<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\User;
use App\Factory\View\Team\TeamItemViewFactory;
use App\Model\View\Item\BatchItemsView;

class BatchItemViewFactory
{
    private ItemViewFactory $itemFactory;

    private TeamItemViewFactory $teamFactory;

    public function __construct(ItemViewFactory $itemFactory, TeamItemViewFactory $teamFactory)
    {
        $this->itemFactory = $itemFactory;
        $this->teamFactory = $teamFactory;
    }

    public function createSingle(User $user): BatchItemsView
    {
        $personalItems = [];
        $sharedItems = [];
        foreach ($user->getPersonalItems() as $item) {
            if (NodeEnumType::TYPE_SYSTEM === $item->getType()) {
                $sharedItems[$item->getId()->toString()] = $item;
                $sharedItems[$item->getRelatedItem()->getId()->toString()] = $item->getRelatedItem();
            } else {
                $personalItems[$item->getId()->toString()] = $item;
            }
        }

        foreach (array_keys($sharedItems) as $id) {
            unset($personalItems[$id]);
        }

        $view = new BatchItemsView();
        $view->setPersonal($this->itemFactory->createCollection(array_values($personalItems)));
        $view->setShared($this->itemFactory->createCollection(array_values($sharedItems)));
        $teamItems = [];
        foreach ($user->getTeams() as $team) {
            $teamItems = array_merge($teamItems, $team->getOwnedItems());
        }
        $view->setTeams($this->itemFactory->createCollection($teamItems));

        return $view;
    }
}
