<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\Entity\UserTeam;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

final class RemoveUserTeamSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $userTeam = $args->getObject();
        if (!$userTeam instanceof UserTeam) {
            return;
        }

        $team = $userTeam->getTeam();
        if (0 === $team->getUserTeams()->count()) {
            $args->getObjectManager()->remove($team);
        }
    }
}
