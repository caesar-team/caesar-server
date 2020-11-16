<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Directory;
use App\Model\View\CredentialsList\ShortListView;

class ShortListViewFactory
{
    public function createSingle(Directory $directory): ShortListView
    {
        $view = new ShortListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getRole());
        $view->setTeamId($directory->getTeam() ? $directory->getTeam()->getId()->toString() : null);

        return $view;
    }

    /**
     * @param Directory[] $users
     *
     * @return ShortListView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
