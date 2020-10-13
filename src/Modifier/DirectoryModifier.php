<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\Directory;
use App\Repository\DirectoryRepository;
use App\Request\Team\EditListRequest;

class DirectoryModifier
{
    private DirectoryRepository $repository;

    public function __construct(DirectoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function modifyByRequest(EditListRequest $request): Directory
    {
        $directory = $request->getDirectory();
        $directory->setLabel($request->getLabel());
        $this->repository->save($directory);

        return $directory;
    }
}
