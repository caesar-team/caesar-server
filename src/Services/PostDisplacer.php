<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Directory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PostDisplacer
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function moveChildPostsToTrash(Directory $directory, User $user)
    {
        $trash = $user->getTrash();

        foreach ($directory->getChildPosts() as $post) {
            $post->setParentList($trash);
            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();
    }
}
