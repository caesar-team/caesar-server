<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemMask;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\Request\ItemCollectionRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ChildItemHandler
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
    /**
     * @var SenderInterface
     */
    private $sender;
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * InviteHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SenderInterface $sender
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $entityManager, SenderInterface $sender, RouterInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->maskRepository = $entityManager->getRepository(ItemMask::class);
        $this->sender = $sender;
        $this->router = $router;
    }

    /**
     * @param ItemCollectionRequest $request
     *
     * @throws \Exception
     */
    public function childItemToItem(ItemCollectionRequest $request)
    {
        foreach ($request->getItems() as $childItem) {
            $item = new Item();
            $item->setParentList($childItem->getUser()->getInbox());
            $item->setOriginalItem($request->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($request->getOriginalItem()->getType());

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($request->getOriginalItem());
    }

    public function updateChildItems(ItemCollectionRequest $request, User $currentOwner): void
    {
        $parentItem = $request->getOriginalItem();
        if (null !== $parentItem->getOriginalItem()) {
            $parentItem = $parentItem->getOriginalItem();
        }

        foreach ($request->getItems() as $childItem) {
            /** @var Item $item */
            /** @var User $user */
            [$item, $user] = $this->getItem($childItem->getUser(), $parentItem);

            if ($currentOwner === $user) {
                $item->setSecret($childItem->getSecret());
            } else {
                $update = $this->extractUpdate($item, $currentOwner);
                $update->setSecret($childItem->getSecret());
            }

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    /**
     * @param ItemCollectionRequest $request
     * @throws \Exception
     */
    public function createMasks(ItemCollectionRequest $request)
    {
        $url = $this->router->generate('google_login', [], RouterInterface::ABSOLUTE_URL);
        foreach ($request->getItems() as $invite) {
            $mask = new ItemMask();
            $mask->setOriginalItem($request->getOriginalItem());
            $mask->setRecipient($invite->getUser());
            $mask->setSecret($invite->getSecret());
            $mask->setAccess($invite->getAccess());

            $this->entityManager->persist($mask);
            $this->sendInvitationMessage($mask, $url);
        }

        $this->entityManager->flush();
    }

    private function sendInvitationMessage(ItemMask $mask, string $url)
    {
        try {
            $this->sender->send(MailRegistry::NEW_ITEM_MESSAGE, [$mask->getRecipient()->getEmail()], [
                'url' => $url,
            ]);
        } catch (\Exception $exception) {
            throw new \LogicException($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
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
}
