<?php

declare(strict_types=1);

namespace App\Services;

use App\Context\ShareFactoryContext;
use App\Model\DTO\ShareItemCollection;
use App\Model\Request\BatchShareRequest;

final class ShareManager
{
    /**
     * @var ShareFactoryContext
     */
    private $shareFactoryContext;

    public function __construct(ShareFactoryContext $shareFactoryContext)
    {
        $this->shareFactoryContext = $shareFactoryContext;
    }

    /**
     * @return ShareItemCollection[]
     */
    public function share(BatchShareRequest $collectionRequest): array
    {
        $shares = [];
        foreach ($collectionRequest->getOriginalItems() as $originalItem) {
            $shares = array_merge($shares, $this->shareFactoryContext->share($originalItem));
        }

        $items = [];
        foreach ($shares as $id => $sharedItems) {
            $items[] = new ShareItemCollection($id, $sharedItems);
        }

        return $items;
    }
}
