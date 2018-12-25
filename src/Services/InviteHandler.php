<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\User;
use App\Model\Request\InviteCollectionRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class InviteHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @param InviteCollectionRequest $request
     *
     * @throws \Exception
     */
    public function inviteToItem(InviteCollectionRequest $request)
    {
        foreach ($request->getInvites() as $invite) {
            $item = $this->getItem($invite->getUser(), $request->getItem());
            $item->setSecret($invite->getSecret());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    private function getItem(User $user, Item $originalItem): Item
    {
        foreach ($originalItem->getSharedItems() as $sharedItem) {
            $owner = $this->userRepository->getByItem($sharedItem);
            if ($user === $owner) {
                return $sharedItem;
            }
        }

        $item = new Item();
        $item->setParentList($user->getInbox());
        $item->setOriginalItem($originalItem);

        return $item;
    }
}
