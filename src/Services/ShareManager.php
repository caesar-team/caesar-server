<?php

declare(strict_types=1);

namespace App\Services;

use App\Context\ShareFactoryContext;
use App\Entity\Item;
use App\Model\Request\BatchShareRequest;

final class ShareManager
{
    /**
     * @var ShareFactoryContext
     */
    private $shareFactoryContext;

    public function __construct(
        ShareFactoryContext $shareFactoryContext
    )
    {
        $this->shareFactoryContext = $shareFactoryContext;
    }

    /**
     * @param BatchShareRequest $collectionRequest
     *
     * @return array|Item[]
     * @throws \Exception
     */
    public function share(BatchShareRequest $collectionRequest): array
    {
        $items = [];
        foreach ($collectionRequest->getOriginalItems() as $originalItem) {
            $items = array_merge($items, $this->shareFactoryContext->share($originalItem));
        }

        return $items;
    }
}