<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Directory;
use App\Entity\Team;
use App\Utils\DefaultIcon;

class TeamFactory
{
    public function create(): Team
    {
        $team = new Team();

        $defaultList = Directory::createDefaultList();
        $defaultList->setTeam($team);

        $lists = Directory::createRootList();
        $lists->setTeam($team);
        $lists->addChildList($defaultList);

        $trash = Directory::createTrash();
        $trash->setTeam($team);

        $team->setLists($lists);
        $team->setTrash($trash);
        $team->setIcon(DefaultIcon::getDefaultIcon());

        return $team;
    }
}
