<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\DirectoryItem;
use App\Entity\Directory\TeamDirectory;
use App\Model\View\CredentialsList\ListView;

class ListViewFactory
{
    public function createSingle(AbstractDirectory $directory): ListView
    {
        $view = new ListView($directory);
        $view->setId($directory->getId()->toString());
        $view->setLabel($directory->getLabel());
        $view->setType($directory->getType());
        $view->setChildren(array_map(function (DirectoryItem $item) {
            return $item->getItem()->getId()->toString();
        }, $directory->getDirectoryItems()));
        $view->setSort($directory->getSort());

        if ($directory instanceof TeamDirectory) {
            $view->setTeamId($directory->getTeam()->getId()->toString());
        }

        $view->setCreatedAt($directory->getCreatedAt());

        return $view;
    }

    /**
     * @param AbstractDirectory[] $users
     *
     * @return ListView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
