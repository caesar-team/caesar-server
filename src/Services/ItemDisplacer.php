<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Directory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ItemDisplacer
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function moveChildItemsToTrash(Directory $directory, User $user)
    {
        $trash = $user->getTrash();

        foreach ($directory->getChildItems() as $item) {
            $item->setParentList($trash);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
