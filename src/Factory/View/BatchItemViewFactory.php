<?php

declare(strict_types=1);

namespace App\Factory\View;


final class BatchItemViewFactory
{
    /**
     * @var ItemViewFactory
     */
    private $itemViewFactory;

    public function __construct(ItemViewFactory $itemViewFactory)
    {
        $this->itemViewFactory = $itemViewFactory;
    }

    /**
     * @param array $items
     * @return \App\Model\View\CredentialsList\ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function create(string $id, array $items)
    {
        return $this->itemViewFactory->createSharedItems($id, $items);
    }
}