<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\TeamDirectory;
use App\Model\View\CredentialsList\ShortListView;

class ShortListViewFactory
{
    public function createSingle(AbstractDirectory $directory): ShortListView
    {
        $view = new ShortListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getType());
        $view->setSort($directory->getSort());

        if ($directory instanceof TeamDirectory) {
            $view->setTeamId($directory->getTeam()->getId()->toString());
        }

        return $view;
    }

    /**
     * @param AbstractDirectory[] $users
     *
     * @return ShortListView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
