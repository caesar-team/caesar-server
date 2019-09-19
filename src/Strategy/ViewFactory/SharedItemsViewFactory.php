<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Model\DTO\SharedItemsContainer;
use App\Model\View\Item\SharedItemsView;

final class SharedItemsViewFactory implements ViewFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof SharedItemsContainer;
    }

    /**
     * @param SharedItemsContainer $data
     *
     * @return mixed
     */
    public function view($data)
    {
        $view = new SharedItemsView();

        return $view;
    }

    /**
     * @param array|SharedItemsContainer[] $data
     *
     * @return mixed
     */
    public function viewList(array $data)
    {
        $list = [];
        foreach ($data as $sharedItemsContainer) {
            $list[] = $this->view($sharedItemsContainer);
        }

        return $list;
    }
}