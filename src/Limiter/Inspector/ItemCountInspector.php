<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use App\Repository\ItemRepository;

final class ItemCountInspector extends AbstractInspector implements InspectorInterface
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUsed(int $addedSize = 0): int
    {
        return $this->repository->getCountOriginalItems() + $addedSize;
    }

    public function getErrorMessage(): string
    {
        return 'limiter.exception.item_count';
    }
}
