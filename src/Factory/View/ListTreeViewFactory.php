<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Model\View\CredentialsList\ListView;
use App\Repository\TeamRepository;

class ListTreeViewFactory
{
    /**
     * @var ItemViewFactory
     */
    private $itemViewFactory;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(ItemViewFactory $itemViewFactory, TeamRepository $teamRepository)
    {
        $this->itemViewFactory = $itemViewFactory;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ListView[]
     */
    public function create(User $user): array
    {
        $lists = $this->getChildren($user->getLists());
        array_push($lists, $this->createInboxView($user->getInbox()));
        array_push($lists, $this->createTrashView($user->getTrash()));

        return $lists;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ListView
     */
    protected function createInboxView(Directory $inbox)
    {
        $view = $this->createListView($inbox);
        $view->type = NodeEnumType::TYPE_INBOX;

        return $view;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ListView
     */
    protected function createTrashView(Directory $inbox)
    {
        $view = $this->createListView($inbox);
        $view->type = NodeEnumType::TYPE_TRASH;

        return $view;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return ListView
     */
    protected function createListView(Directory $directory)
    {
        $view = new ListView();
        $view->id = $directory->getId()->toString();
        $view->label = $directory->getLabel();
        $view->type = NodeEnumType::TYPE_LIST;
        $view->children = $this->getChildren($directory);
        $view->sort = $directory->getSort();
        $team = $this->teamRepository->findOneByDirectory($directory);
        $view->teamId = $team ? $team->getId()->toString() : null;

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
