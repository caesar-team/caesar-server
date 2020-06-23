<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TeamListVoter extends Voter
{
    public const EDIT = 'edit_list';
    public const SORT = 'sort_list';
    public const DELETE = 'delete_list';
    public const CREATE_ITEM = 'create_item_list';

    private UserRepository $userRepository;

    private TeamRepository $teamRepository;

    public function __construct(
        UserRepository $userRepository,
        TeamRepository $teamRepository
    ) {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::EDIT, self::DELETE, self::SORT, self::CREATE_ITEM])) {
            return false;
        }

        if (!$subject instanceof Directory) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        if (!$subject instanceof Directory) {
            return false;
        }
        $team = $this->teamRepository->findOneByDirectory($subject);
        if (null === $team) {
            return false;
        }
        if ($subject->equals($team->getTrash())) {
            return false;
        }

        $userTeam = $team->getUserTeamByUser($user);
        if (null === $userTeam) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $userTeam);
            case self::DELETE:
                return $this->canDelete($subject, $userTeam);
            case self::SORT:
                return $this->canSort($subject, $userTeam);
            case self::CREATE_ITEM:
                return $this->canCreateItem($subject, $userTeam);
        }

        return false;
    }

    private function canEdit(Directory $subject, UserTeam $user): bool
    {
        return $this->canSort($subject, $user) && Directory::LIST_DEFAULT !== $subject->getLabel();
    }

    private function canDelete(Directory $subject, UserTeam $user): bool
    {
        return $this->canEdit($subject, $user);
    }

    private function canSort(Directory $subject, UserTeam $user): bool
    {
        $itemOwner = $this->userRepository->getByList($subject);

        return $user->getUser()->equals($itemOwner)
            || UserTeam::USER_ROLE_ADMIN === $user->getUserRole()
        ;
    }

    private function canCreateItem(Directory $subject, UserTeam $user): bool
    {
        return $this->canSort($subject, $user) || UserTeam::USER_ROLE_MEMBER === $user->getUserRole();
    }
}
