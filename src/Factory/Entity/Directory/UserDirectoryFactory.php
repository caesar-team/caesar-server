<?php

declare(strict_types=1);

namespace App\Factory\Entity\Directory;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\UserDirectory;
use App\Entity\User;
use App\Request\User\CreateListRequest;

class UserDirectoryFactory
{
    public function createFromRequest(CreateListRequest $request): UserDirectory
    {
        $list = new UserDirectory($request->getLabel());
        $list->setType($request->getType());
        $list->setUser($request->getUser());
        if (DirectoryEnumType::LIST === $request->getType()) {
            $list->setParentDirectory($request->getUser()->getLists());
        }

        return $list;
    }

    public function createDefaultDirectories(User $user): array
    {
        $inboxRequest = new CreateListRequest($user);
        $inboxRequest->setLabel(AbstractDirectory::LABEL_INBOX);
        $inboxRequest->setType(DirectoryEnumType::INBOX);
        $inbox = $this->createFromRequest($inboxRequest);

        $trashRequest = new CreateListRequest($user);
        $trashRequest->setLabel(AbstractDirectory::LABEL_TRASH);
        $trashRequest->setType(DirectoryEnumType::TRASH);
        $trash = $this->createFromRequest($trashRequest);

        $rootRequest = new CreateListRequest($user);
        $rootRequest->setLabel(AbstractDirectory::LABEL_ROOT_LIST);
        $rootRequest->setType(DirectoryEnumType::ROOT);
        $root = $this->createFromRequest($rootRequest);

        $defaultRequest = new CreateListRequest($user);
        $defaultRequest->setLabel(AbstractDirectory::LABEL_DEFAULT);
        $defaultRequest->setType(DirectoryEnumType::DEFAULT);
        $default = $this->createFromRequest($defaultRequest);
        $default->setParentDirectory($root);

        return [$inbox, $trash, $root, $default];
    }
}
