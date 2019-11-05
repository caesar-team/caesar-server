<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Repository\ItemUpdateRepository;

final class ItemUpdater
{

    /**
     * @var ItemUpdateRepository
     */
    private $updateRepository;

    public function __construct(ItemUpdateRepository $updateRepository)
    {
        $this->updateRepository = $updateRepository;
    }

    public function createUpdate(Item $item, string $secret, User $currentOwner): void
    {
        $update = $this->extractUpdate($item, $currentOwner);
        $update->setSecret($secret);
        $this->updateRepository->persist($update);
    }

    public function extractUpdate(Item $item, User $user): ItemUpdate
    {
        if ($item->getUpdate()) {
            return $item->getUpdate();
        }

        return new ItemUpdate($item, $user);
    }
}