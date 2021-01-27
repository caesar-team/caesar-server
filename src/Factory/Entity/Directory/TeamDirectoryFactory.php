<?php

declare(strict_types=1);

namespace App\Factory\Entity\Directory;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Team;
use App\Request\Team\CreateListRequest;

class TeamDirectoryFactory
{
    public function createFromRequest(CreateListRequest $request): TeamDirectory
    {
        $list = new TeamDirectory($request->getLabel());
        $list->setType($request->getType());
        $list->setTeam($request->getTeam());
        if (DirectoryEnumType::LIST === $request->getType()) {
            $list->setParentDirectory($request->getTeam()->getLists());
        }

        return $list;
    }

    public function createDefaultDirectories(Team $team): array
    {
        $trashRequest = new CreateListRequest($team);
        $trashRequest->setLabel(AbstractDirectory::LABEL_TRASH);
        $trashRequest->setType(DirectoryEnumType::TRASH);
        $trash = $this->createFromRequest($trashRequest);

        $rootRequest = new CreateListRequest($team);
        $rootRequest->setLabel(AbstractDirectory::LABEL_ROOT_LIST);
        $rootRequest->setType(DirectoryEnumType::ROOT);
        $root = $this->createFromRequest($rootRequest);

        $defaultRequest = new CreateListRequest($team);
        $defaultRequest->setLabel(AbstractDirectory::LABEL_DEFAULT);
        $defaultRequest->setType(DirectoryEnumType::DEFAULT);
        $default = $this->createFromRequest($defaultRequest);
        $default->setParentDirectory($root);

        return [$trash, $root, $default];
    }
}
