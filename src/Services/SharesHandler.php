<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Model\Request\ShareItemRequest;
use Doctrine\ORM\EntityManagerInterface;

class SharesHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ShareItemRequest $request
     *
     * @throws \Exception
     */
    public function shareItem(ShareItemRequest $request)
    {
        $request->getItem()->getSharedItems()->clear();

        foreach ($request->getUsers() as $user) {
            $inbox = $user->getInbox();

            $item = new Item();
            $item->setParentList($inbox);
            $item->setSecret($request->getItem()->getSecret()); // TODO логика и шифрование на фронтовой стороне
            $item->setOriginalItem($request->getItem());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Item $item
     *
     * @throws \Exception
     */
    public function saveItemWithShares(Item $item)
    {
        $this->entityManager->persist($item);
        foreach ($item->getSharedItems() as $sharedItem) {
            $sharedItem->setSecret($item->getSecret());

            $this->entityManager->persist($sharedItem);
        }

        $this->entityManager->flush();
    }
}
