<?php

declare(strict_types=1);

namespace App\EventSubscriber\System;

use App\Event\Team\LeaveTeamEvent;
use App\Repository\ItemRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LeaveTeamSubscriber implements EventSubscriberInterface
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LeaveTeamEvent::class => 'onLeaveTeam',
        ];
    }

    public function onLeaveTeam(LeaveTeamEvent $event): void
    {
        $team = $event->getTeam();
        $user = $event->getUser();

        $keypair = $user->getTeamKeypair($team);
        if (null === $keypair) {
            return;
        }

        $this->repository->remove($keypair);
        $this->repository->flush();
    }
}
