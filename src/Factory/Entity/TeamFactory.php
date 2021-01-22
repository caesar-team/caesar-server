<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Team;
use App\Factory\Entity\Directory\TeamDirectoryFactory;
use App\Utils\DefaultIcon;

class TeamFactory
{
    private TeamDirectoryFactory $directoryFactory;

    public function __construct(TeamDirectoryFactory $directoryFactory)
    {
        $this->directoryFactory = $directoryFactory;
    }

    public function create(): Team
    {
        $team = new Team();
        $team->setIcon(DefaultIcon::getDefaultIcon());
        foreach ($this->directoryFactory->createDefaultDirectories($team) as $directory) {
            $team->addDirectory($directory);
        }

        return $team;
    }
}
