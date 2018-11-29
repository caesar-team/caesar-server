<?php

declare(strict_types=1);

namespace App\Factory\View\Audit;

use App\Model\Response\PaginatedList;

class ListPostEventViewFactory extends AbstractEventViewFactory
{
    /**
     * @var PostEventViewFactory
     */
    private $postEventViewFactory;

    public function __construct(PostEventViewFactory $postEventViewFactory)
    {
        $this->postEventViewFactory = $postEventViewFactory;
    }

    public function create(PaginatedList $paginatedList): PaginatedList
    {
        $list = [];
        foreach ($paginatedList->getData() as $event) {
            $list[] = $this->postEventViewFactory->create($event);
        }

        return new PaginatedList($list, $paginatedList->getTotalPages(), $paginatedList->getTotal());
    }
}
