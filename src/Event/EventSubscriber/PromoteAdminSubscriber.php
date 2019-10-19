<?php

declare(strict_types=1);


namespace App\Event\EventSubscriber;


use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

class PromoteAdminSubscriber implements EventSubscriber
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
            Events::onFlush,
        ];
    }

    /**
     * @param OnFlushEventArgs $args
     * @throws \Exception
     */
    public function onFlush(OnFlushEventArgs  $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $userUpdate) {
            if ($userUpdate instanceof User) {
                $memberships = $userUpdate->getTeams();
                $teams = $this->teamRepository->findAllExcept($memberships);

                if ($this->canAssignTeams($uow, $userUpdate)) {
                    $this->assignTeams($em, $uow, $userUpdate, $teams);
                }
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $userInsert) {
            if ($userInsert instanceof User) {
                $memberships = $userInsert->getTeams();
                $teams = $this->teamRepository->findAllExcept($memberships);

                if (getenv('DOMAIN_ADMIN_EMAIL') === $userInsert->getEmail()) {
                    $this->addAdminRole($em, $uow, $userInsert);
                    $this->assignTeams($em, $uow, $userInsert, $teams);
                }
            }
        }
    }

    private function containsAdminRoles(array $field): bool
    {
        return in_array(User::ROLE_ADMIN, $field[1]) || in_array(User::ROLE_SUPER_ADMIN, $field[1]);
    }

    /**
     * @param EntityManagerInterface $em
     * @param UnitOfWork $uow
     * @param User $user
     * @param array|Team[] $teams
     * @throws \Exception
     */
    private function assignTeams(EntityManagerInterface $em, UnitOfWork $uow, User $user, array $teams): void
    {
        foreach ($teams as $team) {
            $userTeam = new UserTeam($user, $team, UserTeam::USER_ROLE_ADMIN);
            $em->persist($userTeam);
            $classMetadata = $em->getClassMetadata('App\Entity\UserTeam');
            $uow->computeChangeSet($classMetadata, $userTeam);
        }
    }

    private function canAssignTeams(UnitOfWork $uow, User $user): bool
    {
        foreach ($uow->getEntityChangeSet($user) as $keyField => $field) {
            if ('roles' === $keyField && $this->containsAdminRoles($field)) {
                return true;
            }
        }

        return false;
    }

    private function addAdminRole(EntityManagerInterface $em, UnitOfWork $uow, User $user)
    {
        $classMetadata = $em->getClassMetadata('App\Entity\User');
        $user->addRole(User::ROLE_SUPER_ADMIN);
        $uow->recomputeSingleEntityChangeSet($classMetadata, $user);
    }
}