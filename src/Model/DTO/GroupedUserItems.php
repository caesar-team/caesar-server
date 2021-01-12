<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\User;

class GroupedUserItems
{
    private User $user;

    /**
     * @var Item[]
     */
    private array $items;

    /**
     * @var Item[]
     */
    private array $personalItems = [];

    /**
     * @var Item[]
     */
    private array $sharedItems = [];

    /**
     * @var Item[]
     */
    private array $sharedItemsKeypair = [];

    /**
     * @var Item[]
     */
    private array $keypairItems = [];

    /**
     * @var Item[]
     */
    private array $systemItems = [];

    /**
     * @var Item[]
     */
    private array $teamItems = [];

    public function __construct(User $user, array $items)
    {
        $this->user = $user;
        $this->items = $items;
        $this->groupItems();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Item[]
     */
    public function getPersonalItems(): array
    {
        return $this->personalItems;
    }

    /**
     * @return Item[]
     */
    public function getSharedItems(): array
    {
        return $this->sharedItems;
    }

    /**
     * @return Item[]
     */
    public function getKeypairItems(): array
    {
        return $this->keypairItems;
    }

    /**
     * @return Item[]
     */
    public function getSystemItems(): array
    {
        return $this->systemItems;
    }

    /**
     * @return Item[]
     */
    public function getTeamItems(): array
    {
        return $this->teamItems;
    }

    /**
     * @return Item[]
     */
    public function getSharedItemsKeypair(): array
    {
        return $this->sharedItemsKeypair;
    }

    private function groupItems(): void
    {
        foreach ($this->items as $item) {
            switch ($item->getType()) {
                case NodeEnumType::TYPE_KEYPAIR:
                    if (null !== $item->getTeam()
                        && null === $item->getRelatedItem()
                        && !$item->getSignedOwner()->equals($this->user)
                    ) {
                        break;
                    }

                    $this->keypairItems[$item->getId()->toString()] = $item;
                    if (null !== $item->getRelatedItem()) {
                        if ($item->getRelatedItem()->getSignedOwner()->equals($this->user)) {
                            break;
                        }

                        /** @psalm-suppress InvalidPropertyAssignmentValue */
                        $this->sharedItems[$item->getRelatedItem()->getId()->toString()] = $item->getRelatedItem();
                        $this->sharedItemsKeypair[$item->getRelatedItem()->getId()->toString()] = $item;
                    }
                    break;
                case NodeEnumType::TYPE_SYSTEM:
                    $this->systemItems[$item->getId()->toString()] = $item;
                    break;
                default:
                    if (null === $item->getTeam()) {
                        $this->personalItems[$item->getId()->toString()] = $item;
                    } else {
                        $this->teamItems[$item->getId()->toString()] = $item;
                    }
                    break;
            }
        }

        foreach (array_keys($this->sharedItems) as $id) {
            unset($this->personalItems[$id]);
        }
    }
}
