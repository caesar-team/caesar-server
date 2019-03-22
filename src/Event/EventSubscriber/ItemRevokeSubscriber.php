<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ItemRevokeSubscriber implements EventSubscriber
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->entityManager = $entityManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $item = $args->getObject();
        if (!$item instanceof Item) {
            return;
        }
        if (!$this->isChild($item)) {
            return;
        }

        $this->removeAbandonedUser($item);
    }

    /**
     * @param Item $item
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function removeAbandonedUser(Item $item)
    {
        $user = $this->userRepository->getByItem($item);
        if ($user instanceof User && !$user->isFullUser()) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }

    private function isChild(Item $item): bool
    {
        return !is_null($item->getOriginalItem());
    }
}