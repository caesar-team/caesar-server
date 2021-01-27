<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Directory;
use App\Repository\ItemRepository;
use App\Request\Item\BatchMoveItemsCollectionRequest;
use App\Request\Item\MoveItemRequestInterface;

class ItemRelocator
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function move(Directory $directory, MoveItemRequestInterface $request): void
    {
        $item = $request->getItem();
        if (null !== $request->getSecret()) {
            $item->setSecret($request->getSecret());
        }
        if (null !== $request->getRaws()) {
            $item->setRaws($request->getRaws());
        }
        $item->moveTo($directory);
        $this->repository->save($item);
    }

    public function batchMove(BatchMoveItemsCollectionRequest $request): void
    {
        foreach ($request->getMoveItemRequests() as $itemRequest) {
            $this->move($request->getDirectory(), $itemRequest);
        }
    }
}
