<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;
use App\Model\Request\Team\BatchTeamsItemsCollectionRequest;
use Doctrine\ORM\EntityManagerInterface;

final class TeamShareFactory extends AbstractShareFactory
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param BatchTeamsItemsCollectionRequest $data
     *
     * @return array|Item[]
     * @throws \Exception
     */
    public function share($data): array
    {
        $items = [];
        foreach ($data->getShares() as $share) {
            foreach ($share->getItems() as $childItem) {
                $item = new Item($childItem->getUser());
                $item->setParentList($data->getTeam()->getInbox());
                $item->setOriginalItem($share->getOriginalItem());
                $item->setSecret($childItem->getSecret());
                $item->setAccess($childItem->getAccess());
                $item->setType($share->getOriginalItem()->getType());
                $item->setCause($childItem->getCause());
                $item->setStatus($this->getStatusByCause($childItem->getCause()));

                $this->entityManager->persist($item);
                $this->sendItemMessage($childItem);
                $items[$share->getOriginalItem()->getId()->toString()] = $item;
            }
        }

        return $items;
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function canShare($data): bool
    {
        return $data instanceof BatchTeamsItemsCollectionRequest;
    }
}