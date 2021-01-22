<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use App\Entity\Team;
use App\Entity\UserTeam;
use App\Repository\UserTeamRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateTeamSubscriber implements EventSubscriberInterface
{
    private UserTeamRepository $repository;

    public function __construct(UserTeamRepository $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityUpdatedEvent::class => ['preUpdate'],
        ];
    }

    public function preUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $team = $event->getEntityInstance();
        if (!$team instanceof Team) {
            return;
        }

        $userTeams = $this->repository->findMembersByTeam($team);
        $currentUserTeamIds = array_map(static function (UserTeam $userTeam) {
            return $userTeam->getId()->toString();
        }, $userTeams);

        $updateUserTeamIds = array_map(static function (UserTeam $userTeam) {
            return $userTeam->getId()->toString();
        }, $team->getUserTeams()->toArray());

        $removeUserTeams = array_diff($currentUserTeamIds, $updateUserTeamIds);
        foreach ($removeUserTeams as $removeUserTeam) {
            $this->repository->deleteById($removeUserTeam);
        }
    }
}
