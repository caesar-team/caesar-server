<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Directory;
use App\Request\User\CreateListRequest;

class UserDirectoryFactory
{
    public function createFromRequest(CreateListRequest $request): Directory
    {
        $directory = new Directory();
        $directory->setLabel($request->getLabel());
        $directory->setParentList($request->getUser()->getLists());
        $directory->setUser($request->getUser());

        return $directory;
    }
}
