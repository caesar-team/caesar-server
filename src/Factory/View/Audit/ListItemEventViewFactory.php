<?php

declare(strict_types=1);

namespace App\Factory\View\Audit;

use App\Model\Response\PaginatedList;

class ListItemEventViewFactory extends AbstractEventViewFactory
{
    /**
     * @var ItemEventViewFactory
     */
    private $itemEventViewFactory;

    public function __construct(ItemEventViewFactory $itemEventViewFactory)
    {
        $this->itemEventViewFactory = $itemEventViewFactory;
    }

    public function create(PaginatedList $paginatedList): PaginatedList
    {
        $list = [];
        foreach ($paginatedList->getData() as $event) {
            $list[] = $this->itemEventViewFactory->create($event);
        }

        return new PaginatedList($list, $paginatedList->getTotalPages(), $paginatedList->getTotal());
    }
}
