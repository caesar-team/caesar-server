<?php

declare(strict_types=1);


namespace App\Event\EventSubscriber;


use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Services\TeamManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CreateAdminSubscriber implements EventSubscriber
{
    /**
     * @var TeamManager
     */
    private $teamManager;

    public function __construct(TeamManager $teamManager)
    {
        $this->teamManager = $teamManager;
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
        $object = $args->getObject();

        if (!$object instanceof User) {
            return;
        }

        if (getenv('DOMAIN_ADMIN_EMAIL') === $object->getEmail()) {
            $object->addRole(User::ROLE_SUPER_ADMIN);
            $this->teamManager->addTeamToUser($object, UserTeam::USER_ROLE_ADMIN);
        }
    }
}