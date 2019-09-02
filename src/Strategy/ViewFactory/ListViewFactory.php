<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

use App\Entity\Directory;
use App\Factory\View\ItemListViewFactory;
use App\Model\View\Team\ListView;

final class ListViewFactory implements ViewFactoryInterface
{
    /**
     * @var ItemListViewFactory
     */
    private $itemListViewFactory;

    public function __construct(ItemListViewFactory $itemListViewFactory)
    {
        $this->itemListViewFactory = $itemListViewFactory;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canView($data): bool
    {
        return $data instanceof Directory;
    }

    /**
     * @param Directory $data
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function view($data)
    {
        $view = new ListView();
        $view->id = $data->getId()->toString();
        $view->label = $data->getLabel();
        $view->type = $data->getType();
        $view->sort  = $data->getSort();
        $view->children = $this->itemListViewFactory->create($data->getChildItems());

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