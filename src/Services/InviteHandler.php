<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemMask;
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
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $maskRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->maskRepository = $entityManager->getRepository(ItemMask::class);
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
            $item->setType($request->getItem()->getType());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($request->getItem());
    }

    public function updateInvites(InviteCollectionRequest $request, User $currentOwner): void
    {
        $parentItem = $request->getItem();
        if (null !== $parentItem->getOriginalItem()) {
            $parentItem = $parentItem->getOriginalItem();
        }

        foreach ($request->getInvites() as $invite) {
            /** @var Item $item */
            /** @var User $user */
            [$item, $user] = $this->getItem($invite->getUser(), $parentItem);

            if ($currentOwner === $user) {
                $item->setSecret($invite->getSecret());
            } else {
                $update = $this->extractUpdate($item, $currentOwner);
                $update->setSecret($invite->getSecret());
            }

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    private function getItem(User $user, Item $originalItem): array
    {
        $owner = $this->userRepository->getByItem($originalItem);
        if ($user === $owner) {
            return [$originalItem, $user];
        }

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

    /**
     * @param InviteCollectionRequest $request
     * @throws \Exception
     */
    public function setMasks(InviteCollectionRequest $request)
    {
        foreach ($request->getInvites() as $invite) {
            $mask = new ItemMask();
        }
    }
}
