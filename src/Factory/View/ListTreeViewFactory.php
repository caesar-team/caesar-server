<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Model\View\CredentialsList\ListView;

class ListTreeViewFactory
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
     * @param User $user
     *
     * @return ListView[]
     */
    public function create(User $user): array
    {
        return [
            $this->createInboxView($user->getInbox()),
            $this->getChildren($user->getLists()),
            $this->createTrashView($user->getTrash()),
        ];
    }

    protected function createInboxView(Directory $inbox)
    {
        $view = $this->createListView($inbox);
        $view->type = NodeEnumType::TYPE_INBOX;

        return $view;
    }

    protected function createTrashView(Directory $inbox)
    {
        $view = $this->createListView($inbox);
        $view->type = NodeEnumType::TYPE_TRASH;

        return $view;
    }

    protected function createListView(Directory $directory)
    {
        $view = new ListView();
        $view->id = $directory->getId();
        $view->label = $directory->getLabel();
        $view->type = NodeEnumType::TYPE_LIST;
        $view->children = $this->getChildren($directory);
        $view->sort = $directory->getSort();

        return $view;
    }

    protected function getChildren(Directory $directory)
    {
        return array_merge(
            array_map([$this, 'createListView'], $directory->getChildLists()->toArray()),
            array_map([$this->itemViewFactory, 'create'], $directory->getChildItems(Item::STATUS_FINISHED))
        );
    }
}
