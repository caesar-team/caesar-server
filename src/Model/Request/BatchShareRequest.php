<?php

declare(strict_types=1);

namespace App\Model\Request;

final class BatchShareRequest
{
    /**
     * @var ItemCollectionRequest[]
     */
    private $originalItems = [];

    /**
     * @return ItemCollectionRequest[]
     */
    public function getOriginalItems(): array
    {
        return $this->originalItems;
    }

    public function setOriginalItems(array $originalItems): void
    {
        $this->originalItems = $originalItems;
    }
}