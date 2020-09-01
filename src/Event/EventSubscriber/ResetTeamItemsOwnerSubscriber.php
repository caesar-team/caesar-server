<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Repository\ItemRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
            BeforeEntityDeletedEvent::class => ['preRemove'],
        ];
    }

    public function preRemove(BeforeEntityDeletedEvent $event): void
    {
        $user = $event->getEntityInstance();
        if (!$user instanceof User) {
            return;
        }

        foreach ($user->getUserTeams() as $userTeam) {
            $team = $userTeam->getTeam();
            $admins = $userTeam->getTeam()->getAdminUserTeams([$user->getId()->toString()]);
            if (0 == count($admins)) {
                $members = $team->getMemberUserTeams();
                if (0 === count($members)) {
                    continue;
                }

                $nextUserTeam = current($members);
            } else {
                $nextUserTeam = current($admins);
            }

            $this->repository->resetOwnerTeamItems($team, $user, $nextUserTeam->getUser());
        }
    }
}
