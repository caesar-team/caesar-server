<?php

declare(strict_types=1);

namespace App\Model\Request;

class BatchShareRequest
{
    /**
     * @var BatchItemCollectionRequest[]
     */
    private $originalItems = [];

    /**
     * @return array|BatchItemCollectionRequest[]
     */
    public function getOriginalItems(): array
    {
        return $this->originalItems;
    }

    public function setOriginalItems(array $originalItems): void
    {
        $this->originalItems = $originalItems;
    }

    public function addOriginalItem(BatchItemCollectionRequest $request): void
    {
        $this->originalItems[] = $request;
    }
}
