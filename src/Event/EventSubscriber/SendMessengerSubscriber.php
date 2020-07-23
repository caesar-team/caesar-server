<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Event\User\RegistrationCompletedEvent;
use App\Mailer\MailRegistry;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Repository\TeamRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendMessengerSubscriber implements EventSubscriberInterface
{
    private MessengerInterface $messenger;

    private TeamRepository $repository;

    public function __construct(MessengerInterface $messenger, TeamRepository $repository)
    {
        $this->messenger = $messenger;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RegistrationCompletedEvent::class => 'onRegistrationCompleted',
        ];
    }

    public function onRegistrationCompleted(RegistrationCompletedEvent $event): void
    {
        $user = $event->getUser();
        $defaultTeam = $this->repository->getDefaultTeam();
        if (null === $defaultTeam) {
            return;
        }

        foreach ($defaultTeam->getAdminUserTeams() as $admin) {
            if (null === $admin->getUser()) {
                continue;
            }

            $message = Message::createDeferredFromUser(
                $admin->getUser(),
                MailRegistry::NEW_REGISTRATION,
                ['email' => $user->getEmail()],
            );

            $this->messenger->send($message);
        }
    }
}
