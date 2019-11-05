<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Message\BufferedMessage;
use App\Entity\User;
use App\Event\ShareEvent;
use App\Event\SharesFlushEvent;
use App\Mailer\MailRegistry;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Security\AuthorizationManager\InvitationEncoder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ShareSubscriber implements EventSubscriberInterface
{
    private $shares = [];
    /**
     * @var MessageBusInterface
     */
    private $messageBus;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InvitationRepository
     */
    private $invitationRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        UserRepository $userRepository,
        InvitationRepository $invitationRepository
    )
    {

        $this->messageBus = $messageBus;
        $this->userRepository = $userRepository;
        $this->invitationRepository = $invitationRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            ShareEvent::class => 'onShare',
            SharesFlushEvent::class => 'onSharesFlush',
        ];
    }

    public function onShare(ShareEvent $event): void
    {
        $item = $event->getItem();
        $hash = (InvitationEncoder::initEncoder())->encode($item->getSignedOwner()->getEmail());
        $invitation = $this->invitationRepository->findOneFreshByHash($hash);
        if ($invitation) {
            return;
        }

        $this->shares[$item->getSignedOwner()->getId()->toString()][] = $item;
    }

    public function onSharesFlush(SharesFlushEvent $event): void
    {
        foreach ($this->shares as $recipientId => $items) {
            /** @var User $user */
            if (!$user = $this->userRepository->find($recipientId)) {
                continue;
            }

            $content = ['itemsCount' => count($items)];
            $this->messageBus->dispatch(new BufferedMessage(MailRegistry::NEW_ITEM_MESSAGE, [$user->getEmail()], json_encode($content)));
        }
    }
}