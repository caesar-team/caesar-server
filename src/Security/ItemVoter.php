<?php

declare(strict_types=1);

namespace App\Security;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const DELETE_ITEM = 'delete_item';
    public const CREATE_ITEM = 'create_item';
    public const EDIT_ITEM = 'edit_item';
    public const SHOW_ITEM = 'show_item';
    public const MOVE_ITEM = 'move_item';
    private const ATTRIBUTES = [
        self::DELETE_ITEM,
        self::CREATE_ITEM,
        self::EDIT_ITEM,
        self::SHOW_ITEM,
        self::MOVE_ITEM,
    ];
    private const AVAILABLE_TEAM_ROLES = [
        UserTeam::USER_ROLE_ADMIN,
        UserTeam::USER_ROLE_MEMBER,
    ];

    /**
     * @var UserTeamRepository
     */
    private $userTeamRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(UserTeamRepository $userTeamRepository, TeamRepository $teamRepository)
    {
        $this->userTeamRepository = $userTeamRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof Item) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Item   $subject
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (in_array($attribute, self::ATTRIBUTES)) {
            $itemOwner = $subject->getSignedOwner();
            $userTeam = $this->findUserTeam($subject, $user);
            switch ($attribute) {
                case self::MOVE_ITEM:
                    return $this->canMove($subject, $user);
                case self::EDIT_ITEM:
                    return $this->canEdit($subject, $user);
                case self::CREATE_ITEM:
                    return ($userTeam instanceof UserTeam && in_array($userTeam->getUserRole(), self::AVAILABLE_TEAM_ROLES))
                        || User::FLOW_STATUS_FINISHED === $user->getFlowStatus()
                    ;
                case self::DELETE_ITEM:
                    $teamUserRole = $userTeam instanceof UserTeam ? $userTeam->getUserRole() : null;
                    $isAdmin = $user->hasRole(User::ROLE_ADMIN) || UserTeam::USER_ROLE_ADMIN === $teamUserRole;

                    return $itemOwner === $user || $isAdmin;
                case self::SHOW_ITEM:
                    return true;
                default:
                    return false;
            }
        }

        throw new LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function findUserTeam(Item $item, User $user): ?UserTeam
    {
        $team = $this->teamRepository->findOneByDirectory($item->getParentList());
        if (is_null($team)) {
            return null;
        }

        return $this->userTeamRepository->findOneByUserAndTeam($user, $team);
    }

    private function canEdit(Item $item, User $currentUser): bool
    {
        $userTeam = $this->findUserTeam($item, $currentUser);
        $hasAccess = AccessEnumType::TYPE_READ !== $item->getAccess();
        if (is_null($userTeam)) {
            return
                ($item->getOwner() === $currentUser)
                || $hasAccess;
        }

        return (UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole()) || $hasAccess;
    }

    private function canMove(Item $item, User $currentUser): bool
    {
        if ($currentUser->hasOneOfRoles([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])) {
            return true;
        }

        if ($currentUser->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return false;
        }

        $userTeam = $this->findUserTeam($item, $currentUser);
        if ($userTeam && UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole()) {
            return true;
        }

        if (null === $item->getPreviousList()) {
            return false;
        }

        if (null === $item->getParentList()) {
            return false;
        }

        $prevDirectoryTeam = $this->teamRepository->findOneByDirectory($item->getPreviousList());
        $currDirectoryTeam = $this->teamRepository->findOneByDirectory($item->getParentList());

        if (is_null($prevDirectoryTeam) && $currDirectoryTeam && UserTeam::USER_ROLE_ADMIN !== $userTeam->getUserRole()) {
            return false; //false if personal and just member
        }

        return $item->getSignedOwner() === $currentUser;
    }
}
