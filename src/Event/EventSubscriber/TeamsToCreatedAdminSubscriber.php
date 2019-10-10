<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

final class TeamsToCreatedAdminSubscriber implements EventSubscriber
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $user = $args->getObject();
        if (!$user instanceof User) {
            return;
        }

        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $teams = $this->teamRepository->findAll();

            foreach ($teams as $team) {
                $userTeam = new UserTeam($user, $team, UserTeam::USER_ROLE_ADMIN);
                $user->addUserTeam($userTeam);
            }
        }
    }
}