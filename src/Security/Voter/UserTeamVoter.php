<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\UserTeamRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

final class UserTeamVoter extends Voter
{
    public const USER_TEAM_LEAVE = 'leave';
    public const USER_TEAM_EDIT   = 'edit';
    public const USER_TEAM_VIEW   = 'view';
    public const USER_TEAM_REMOVE_MEMBER = 'remove_member';

    private const ROLES_TO_VIEW = [
        UserTeam::USER_ROLE_ADMIN,
        UserTeam::USER_ROLE_MEMBER,
    ];

    /**
     * @var Security
     */
    private $security;
    /**
     * @var UserTeamRepository
     */
    private $userTeamRepository;

    public function __construct(Security $security, UserTeamRepository $userTeamRepository)
    {
        $this->security = $security;
        $this->userTeamRepository = $userTeamRepository;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::USER_TEAM_LEAVE, self::USER_TEAM_EDIT, self::USER_TEAM_VIEW])) {
            return false;
        }

        if (!$subject instanceof Team) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Team $subject
     * @param TokenInterface $token
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userTeam = $this->userTeamRepository->findOneByUserAndTeam($user, $subject);

        if (!$userTeam instanceof UserTeam) {
            return false;
        }

        switch ($attribute) {
            case self::USER_TEAM_REMOVE_MEMBER:
                return UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole() || $user->hasRole(User::ROLE_ADMIN);
            case self::USER_TEAM_LEAVE:
                return true;
            case self::USER_TEAM_EDIT:
                return UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole() || $user->hasRole(User::ROLE_ADMIN);
            case self::USER_TEAM_VIEW:
                return in_array($userTeam->getUserRole(), self::ROLES_TO_VIEW) || $user->hasRole(User::ROLE_ADMIN);
            default:
                return false;
        }
    }
}