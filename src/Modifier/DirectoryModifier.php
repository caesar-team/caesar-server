<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\Directory;
use App\Repository\DirectoryRepository;
use App\Request\EditListRequestInterface;
use App\Request\User\SortListRequest;

class DirectoryModifier
{
    private DirectoryRepository $repository;

    public function __construct(DirectoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function modifySortByRequest(SortListRequest $request): Directory
    {
        $directory = $request->getDirectory();
        $directory->setSort($request->getSort());
        $this->repository->save($directory);

        return $directory;
    }

    public function modifyByRequest(EditListRequestInterface $request): Directory
    {
        $directory = $request->getDirectory();
        $directory->setLabel($request->getLabel());
        $this->repository->save($directory);

        return $directory;
    }
}
