<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Directory;
use App\Entity\Item;
use App\Model\View\CredentialsList\ListView;
use App\Repository\TeamRepository;

class ListViewFactory
{
    private TeamRepository $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function createSingle(Directory $directory): ListView
    {
        $team = $this->teamRepository->findOneByDirectory($directory);

        $view = new ListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getType());
        $view->setChildren(array_map(function (Item $item) {
            return $item->getId()->toString();
        }, $directory->getChildItems()));
        $view->setSort($directory->getSort());
        $view->setTeamId($team ? $team->getId()->toString() : null);
        $view->setCreatedAt($directory->getCreatedAt());

        return $view;
    }

    /**
     * @param Directory[] $users
     *
     * @return ListView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
