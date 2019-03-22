<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\Request\ChildItem;
use App\Model\Request\ItemCollectionRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
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
     * @var SenderInterface
     */
    private $sender;
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var Producer
     */
    private $producer;

    /**
     * InviteHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SenderInterface $sender
     * @param RouterInterface $router
     * @param Producer $producer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SenderInterface $sender,
        RouterInterface $router,
        Producer $producer
    )
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->sender = $sender;
        $this->router = $router;
        $this->producer = $producer;
    }

    /**
     * @param ItemCollectionRequest $request
     *
     * @return array
     * @throws \Exception
     */
    public function childItemToItem(ItemCollectionRequest $request)
    {
        $url = $this->router->generate('google_login', [], RouterInterface::ABSOLUTE_URL);
        $items = [];
        foreach ($request->getItems() as $childItem) {
            $item = new Item();
            $item->setParentList($childItem->getUser()->getInbox());
            $item->setOriginalItem($request->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($request->getOriginalItem()->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));

            $this->entityManager->persist($item);
            $this->sendInvitationMessage($childItem, $url);
            $items[] = $item;
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($request->getOriginalItem());

        return $items;
    }

    /**
     * @param ItemCollectionRequest $request
     * @param User $currentOwner
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

            if ($currentOwner === $user || Item::CAUSE_SHARE === $item->getCause()) {
                $item->setSecret($childItem->getSecret());
            } else {
                $update = $this->extractUpdate($item, $currentOwner);
                $update->setSecret($childItem->getSecret());
            }
            if ($childItem->getLink()) {
                $item->setLink($childItem->getLink());
            }

            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    private function sendInvitationMessage(ChildItem $childItem, string $url)
    {
        if ($childItem->getUser()->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        try {
            $msg = array('email' => $childItem->getUser()->getEmail(), 'url' => $url, 'email_code' => MailRegistry::NEW_ITEM_MESSAGE);
            $this->producer->setContentType('application/json');
            $this->producer->publish(json_encode($msg), 'send_message_producer');
        } catch (\Exception $exception) {
            throw new \LogicException($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param User $user
     * @param Item $originalItem
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    private function getStatusByCause(string $cause): string
    {
        switch ($cause) {
            case Item::CAUSE_INVITE:
                $status = Item::STATUS_OFFERED;
                break;
            default:
                $status = Item::STATUS_FINISHED;
        }

        return $status;
    }
}
