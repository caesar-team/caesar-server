<?php

declare(strict_types=1);

namespace App\Factory\View\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\User;
use App\Factory\View\Team\TeamItemViewFactory;
use App\Model\View\Item\BatchItemsView;
use App\Model\View\Item\ItemView;

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
        $items = $user->getPersonalItems();
        foreach ($user->getTeams() as $team) {
            $items = array_merge($items, $team->getOwnedItems());
        }

        $personalItems = $sharedItems = $keypairItems = $systemItems = $teamItems = [];
        foreach ($items as $item) {
            switch ($item->getType()) {
                case NodeEnumType::TYPE_KEYPAIR:
                    if (!$item->getSignedOwner()->equals($user)) {
                        break;
                    }

                    $keypairItems[$item->getId()->toString()] = $item;
                    if (null !== $item->getRelatedItem()) {
                        $sharedItems[$item->getRelatedItem()->getId()->toString()] = $item->getRelatedItem();
                    }
                    break;
                case NodeEnumType::TYPE_SYSTEM:
                    $systemItems[$item->getId()->toString()] = $item;
                    break;
                default:
                    if (null === $item->getTeam()) {
                        $personalItems[$item->getId()->toString()] = $item;
                    } else {
                        $teamItems[$item->getId()->toString()] = $item;
                    }
                    break;
            }
        }

        foreach (array_keys($sharedItems) as $id) {
            unset($personalItems[$id]);
        }

        $view = new BatchItemsView();
        $view->setPersonals($this->itemFactory->createCollection(array_values($personalItems)));
        /** @psalm-suppress InvalidArgument */
        $view->setShares(
            // @todo remove after implemented inbox
            array_map(
                static function (ItemView $view) use ($user) {
                    $view->setListId($user->getInbox()->getId()->toString());

                    return $view;
                },
                $this->itemFactory->createCollection(array_values($sharedItems))
            )
        );
        $view->setTeams($this->itemFactory->createCollection(array_values($teamItems)));
        $view->setSystems($this->itemFactory->createCollection(array_values($systemItems)));
        $view->setKeypairs($this->itemFactory->createCollection(array_values($keypairItems)));

        return $view;
    }
}
