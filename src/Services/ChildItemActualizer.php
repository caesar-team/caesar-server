<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\Request\ChildItem;
use App\Model\Request\ItemCollectionRequest;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated
 */
class ChildItemActualizer
{
    public const URL_ROOT = 'root';
    public const EVENT_NEW_ITEM = 'new';
    public const EVENT_UPDATED_ITEM = 'updated';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SenderInterface
     */
    private $sender;
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var MessengerInterface
     */
    private $messenger;
    /**
     * @var string
     */
    private $absoluteUrl;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InviteHandler constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SenderInterface $sender,
        RouterInterface $router,
        MessengerInterface $messenger,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->sender = $sender;
        $this->router = $router;
        $this->messenger = $messenger;
        $this->absoluteUrl = $this->router->generate(self::URL_ROOT, [], RouterInterface::ABSOLUTE_URL);
        $this->logger = $logger;
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
            if ($currentOwner !== $user) {
                $this->sendItemMessage($childItem);
            }
        }

        $this->entityManager->flush();
    }

    private function sendItemMessage(ChildItem $childItem): void
    {
        if ($childItem->getUser()->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        $this->messenger->send(
            Message::createDeferredFromUser(
                $childItem->getUser(),
                MailRegistry::UPDATE_ITEM,
                ['url' => $this->absoluteUrl, 'update_count' => 1]
            )
        );

        $this->logger->debug('Registered in ChildItemHandler');
    }

    private function getItem(User $user, Item $originalItem): array
    {
        $owner = $originalItem->getSignedOwner();
        if ($user === $owner) {
            return [$originalItem, $user];
        }

        foreach ($originalItem->getSharedItems() as $sharedItem) {
            $owner = $sharedItem->getSignedOwner();
            if ($user === $owner) {
                return [$sharedItem, $user];
            }
        }

        throw new LogicException('No Such user in original invite '.$user->getId()->toString());
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function extractUpdate(Item $item, User $user): ItemUpdate
    {
        if (null !== $item->getUpdate()) {
            /** @psalm-suppress NullableReturnStatement */
            return $item->getUpdate();
        }

        return new ItemUpdate($item, $user);
    }
}
