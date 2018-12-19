<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Item;
use App\Model\View\CredentialsList\ItemView;

class ItemListViewFactory
{
    /**
     * @var ItemViewFactory
     */
    private $secretViewFactory;

    public function __construct(ItemViewFactory $secretViewFactory)
    {
        $this->secretViewFactory = $secretViewFactory;
    }

    /**
     * @param Item[] $itemCollection
     *
     * @return ItemView[]
     */
    public function create(array $itemCollection): array
    {
        $viewCollection = [];
        foreach ($itemCollection as $item) {
            $viewCollection[] = $this->secretViewFactory->create($item);
        }

        return $viewCollection;
    }
}
