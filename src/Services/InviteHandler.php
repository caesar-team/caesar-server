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
            $item = new Item();
            $item->setParentList($invite->getUser()->getInbox());
            $item->setOriginalItem($request->getItem());
            $item->setSecret($invite->getSecret());
            $item->setAccess($invite->getAccess());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
