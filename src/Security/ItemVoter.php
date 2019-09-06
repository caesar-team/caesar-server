<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Repository\UserTeamRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const DELETE_ITEM = 'delete_item';
    public const CREATE_ITEM = 'create_item';
    public const EDIT_ITEM = 'edit_item';
    public const SHOW_ITEM = 'show_item';
    private const AVAILABLE_TEAM_ROLES = [
        UserTeam::USER_ROLE_ADMIN,
        UserTeam::USER_ROLE_MEMBER,
    ];

    /** @var UserRepository */
    private $userRepository;
    /**
     * @var UserTeamRepository
     */
    private $userTeamRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(UserRepository $userRepository, UserTeamRepository $userTeamRepository, TeamRepository $teamRepository)
    {
        $this->userRepository = $userRepository;
        $this->userTeamRepository = $userTeamRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
            return false;
        }

        if (!$subject instanceof Item) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Item $subject
     * @param TokenInterface $token
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
            $itemOwner = $this->userRepository->getByItem($subject);
            $userTeam = $this->findUserTeam($subject, $user);
            switch ($attribute) {
                case self::EDIT_ITEM:
                    return $itemOwner === $user;
                case self::CREATE_ITEM && $userTeam instanceof UserTeam:
                    return  in_array($userTeam->getUserRole(), self::AVAILABLE_TEAM_ROLES);
                case self::CREATE_ITEM:
                    return User::FLOW_STATUS_FINISHED === $user->getFlowStatus();
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

        throw new \LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }

    /**
     * @param Item $item
     * @param User $user
     * @return UserTeam|null
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
}
