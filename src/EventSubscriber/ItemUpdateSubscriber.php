<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Item;
use App\Entity\Message\BufferedMessage;
use App\Entity\User;
use App\Event\ItemUpdateEvent;
use App\Event\ItemUpdatesFlushEvent;
use App\Mailer\MailRegistry;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ItemUpdateSubscriber implements EventSubscriberInterface
{
    private $updates = [];
    /**
     * @var MessageBusInterface
     */
    private $messageBus;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(MessageBusInterface $messageBus, UserRepository $userRepository)
    {

        $this->messageBus = $messageBus;
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ItemUpdateEvent::class => 'onItemUpdate',
            ItemUpdatesFlushEvent::class => 'onItemUpdatesFlush',
        ];
    }

    public function onItemUpdate(ItemUpdateEvent $itemUpdate)
    {
        $item = $itemUpdate->getItem();
        $this->updates[$item->getSignedOwner()->getId()->toString()][] = $item;
    }

    public function onItemUpdatesFlush()
    {
        foreach ($this->updates as $recipientId => $items) {
            /** @var User $user */
            if (!$user = $this->userRepository->find($recipientId)) {
                continue;
            }

            $content = ['itemsCount' => count($items)];
            $this->messageBus->dispatch(new BufferedMessage(MailRegistry::UPDATED_ITEM_MESSAGE, [$user->getEmail()], json_encode($content)));
        }
    }
}