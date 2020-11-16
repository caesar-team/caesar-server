<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Directory;
use App\Request\Team\CreateListRequest;

class TeamDirectoryFactory
{
    public function createFromRequest(CreateListRequest $request): Directory
    {
        $directory = new Directory();
        $directory->setLabel($request->getLabel());
        $directory->setParentList($request->getTeam()->getLists());
        $directory->setTeam($request->getTeam());

        return $directory;
    }
}
