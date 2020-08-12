<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Event\User\RegistrationCompletedEvent;
use App\Repository\UserRepository;
use App\Team\DefaultTeamUserAdder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PromoteAdminSubscriber implements EventSubscriberInterface
{
    private DefaultTeamUserAdder $teamUserAdder;

    private UserRepository $repository;

    private string $domainAdminEmail;

    public function __construct(DefaultTeamUserAdder $teamUserAdder, UserRepository $repository, string $domainAdminEmail = '')
    {
        $this->teamUserAdder = $teamUserAdder;
        $this->repository = $repository;
        $this->domainAdminEmail = $domainAdminEmail;
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
        if ($this->domainAdminEmail !== $user->getEmail()) {
            return;
        }
        
        $user->addRole(User::ROLE_ADMIN);
        $this->repository->save($user);

        $this->teamUserAdder->addUser($user, UserTeam::USER_ROLE_ADMIN);
    }
}
