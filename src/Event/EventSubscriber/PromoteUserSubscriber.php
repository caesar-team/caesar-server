<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Services\TeamManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PromoteUserSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    private TeamRepository $teamRepository;

    private TeamManager $teamManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        TeamRepository $teamRepository,
        TeamManager $teamManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $teamRepository;
        $this->teamManager = $teamManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityUpdatedEvent::class => ['preUpdated'],
        ];
    }

    public function preUpdated(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        if (!$entity instanceof User) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changes = $unitOfWork->getEntityChangeSet($entity);
        if (!in_array(User::ROLE_ADMIN, $changes['roles'][0])
            && in_array(User::ROLE_ADMIN, $changes['roles'][1])) {
            $teams = $this->teamRepository->findAll();
            foreach ($teams as $team) {
                $userTeam = $team->getUserTeamByUser($entity);
                if (null !== $userTeam && $userTeam->hasRole(UserTeam::USER_ROLE_ADMIN)) {
                    continue;
                }

                if (null === $userTeam) {
                    $userTeam = new UserTeam($entity, $team);
                }

                $userTeam->setUserRole(UserTeam::USER_ROLE_ADMIN);
                $this->entityManager->persist($userTeam);
            }

            $this->entityManager->flush();
        }
    }
}
