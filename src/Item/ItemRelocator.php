<?php

declare(strict_types=1);

namespace App\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\TeamDirectory;
use App\Request\Item\MovePersonalItemRequestInterface;
use App\Request\Team\MoveTeamItemRequestInterface;
use Doctrine\ORM\EntityManagerInterface;

class ItemRelocator implements ItemRelocatorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function moveChildItems(AbstractDirectory $fromDirectory, AbstractDirectory $toDirectory): void
    {
        foreach ($fromDirectory->getDirectoryItems() as $item) {
            $item->setDirectory($toDirectory);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    public function movePersonalItem(MovePersonalItemRequestInterface $request): void
    {
        $directory = $request->getDirectory();
        $item = $request->getItem();
        $user = $request->getUser();
        if (null !== $request->getSecret()) {
            $item->setSecret($request->getSecret());
        }

        $directoryItem = $item->getCurrentDirectoryItemByUser($user);
        if (null === $directoryItem) {
            throw new \BadMethodCallException('Could not find current directory by user');
        }

        $directoryItem->setDirectory($directory);
        $this->entityManager->persist($directoryItem);

        if ($directory instanceof TeamDirectory) {
            $item->setTeam($directory->getTeam());
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function moveTeamItem(MoveTeamItemRequestInterface $request): void
    {
        $directory = $request->getDirectory();
        $item = $request->getItem();
        $team = $request->getTeam();
        if (null !== $request->getSecret()) {
            $item->setSecret($request->getSecret());
        }

        $directoryItem = $item->getCurrentDirectoryItemByTeam($team);
        if (null === $directoryItem) {
            throw new \BadMethodCallException('Could not find current directory by team');
        }
        $directoryItem->setDirectory($directory);

        if ($directory instanceof TeamDirectory) {
            $item->setTeam($directory->getTeam());
        } else {
            $item->setTeam(null);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
}
