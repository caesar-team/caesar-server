<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Directory;
use App\Model\View\Team\ListView;

final class ListViewFactory implements ViewFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return false; //manual
    }

    /**
     * @param Directory $data
     *
     * @return mixed
     */
    public function view($data)
    {
        $view = new ListView();
        $view->id = $data->getId()->toString();
        $view->label = $data->getLabel();
        $view->type = $data->getType();
        $view->sort  = $data->getSort();

        return $view;
    }

    /**
     * @param Directory[] $data
     *
     * @return mixed
     */
    public function viewList(array $data)
    {
        $list = [];
        foreach ($data as $directory) {
            $list[] = $this->view($directory);
        }

        return $list;
    }
}