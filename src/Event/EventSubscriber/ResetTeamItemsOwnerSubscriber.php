<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\ItemRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class ResetTeamItemsOwnerSubscriber implements EventSubscriberInterface
{
    private ItemRepository $repository;

    public function __construct(ItemRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_REMOVE => ['preRemove'],
        ];
    }

    public function preRemove(GenericEvent $event): void
    {
        $user = $event->getSubject();
        if (!$user instanceof User) {
            return;
        }

        foreach ($user->getUserTeams() as $userTeam) {
            $team = $userTeam->getTeam();
            $admins = $userTeam->getTeam()->getAdminUserTeams([$user->getId()->toString()]);
            if (0 === count($admins)) {
                /** @var UserTeam $nextUserTeam */
                $nextUserTeam = $team->getUserTeams()->first();
                if (!$nextUserTeam instanceof UserTeam) {
                    continue;
                }
            } else {
                $nextUserTeam = current($admins);
            }

            $this->repository->resetOwnerTeamItems($team, $user, $nextUserTeam->getUser());
        }
    }
}
