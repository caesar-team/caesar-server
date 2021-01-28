<?php

declare(strict_types=1);

namespace App\EventSubscriber\System;

use App\Event\User\RegistrationCompletedEvent;
use App\Mailer\FosUserMailer;
use App\Mailer\MailRegistry;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Repository\TeamRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SendMessengerSubscriber implements EventSubscriberInterface
{
    private MessengerInterface $messenger;

    private TeamRepository $repository;

    private RouterInterface $router;

    private FosUserMailer $fosUserMailer;

    public function __construct(
        MessengerInterface $messenger,
        TeamRepository $repository,
        RouterInterface $router,
        FosUserMailer $fosUserMailer
    ) {
        $this->messenger = $messenger;
        $this->repository = $repository;
        $this->router = $router;
        $this->fosUserMailer = $fosUserMailer;
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

        $teamSettingLink = $this->router->generate('front_setting_team', ['team' => $defaultTeam->getId()->toString()], UrlGeneratorInterface::ABSOLUTE_URL);
        foreach ($defaultTeam->getAdminUserTeams() as $admin) {
            if (null === $admin->getUser()) {
                continue;
            }

            $message = Message::createDeferredFromUser(
                $admin->getUser(),
                MailRegistry::NEW_REGISTRATION,
                [
                    'email' => $user->getEmail(),
                    'url' => $teamSettingLink,
                ],
            );

            $this->messenger->send($message);
        }

        if (!$user->isEnabled()) {
            $this->fosUserMailer->sendConfirmationEmailMessage($user);
        }
    }
}
