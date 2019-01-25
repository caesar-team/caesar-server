<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
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
            $item = new Item();
            $item->setParentList($invite->getUser()->getInbox());
            $item->setOriginalItem($request->getItem());
            $item->setSecret($invite->getSecret());
            $item->setAccess($invite->getAccess());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    /**
     * @param InviteCollectionRequest $request
     *
     * @throws \Exception
     */
    public function updateInvites(InviteCollectionRequest $request)
    {
        foreach ($request->getInvites() as $invite) {
            /** @var Item $item */
            /** @var User $user */
            [$item, $user] = $this->getItem($invite->getUser(), $request->getItem());

            $update = $this->extractUpdate($item, $user);
            $update->setSecret($invite->getSecret());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    private function getItem(User $user, Item $originalItem): array
    {
        foreach ($originalItem->getSharedItems() as $sharedItem) {
            $owner = $this->userRepository->getByItem($sharedItem);
            if ($user === $owner) {
                return [$sharedItem, $user];
            }
        }

        throw new \LogicException('No Such user in original invite '.$user->getId()->toString());
    }

    private function extractUpdate(Item $item, User $user): ItemUpdate
    {
        if ($item->getUpdate()) {
            return $item->getUpdate();
        }

        return new ItemUpdate($item, $user);
    }
}
