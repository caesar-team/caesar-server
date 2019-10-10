<?php

declare(strict_types=1);

namespace App\Security\Voter;


use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class TeamVoter extends Voter
{
    public const TEAM_CREATE = 'team_create';
    /**
     * @var User
     */
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
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
        if (self::TEAM_CREATE !== $attribute) {
            return false;
        }

        if (!$subject instanceof Team || is_null($this->user)) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Team $team
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $team, TokenInterface $token)
    {
        if (self::TEAM_CREATE !== $attribute) {
            return false;
        }

        if (Team::DEFAULT_GROUP_ALIAS === $team->getAlias()) {
            return false;
        }

        return $this->user->hasRole(User::ROLE_ADMIN) || $this->user->hasRole(User::ROLE_SUPER_ADMIN);
    }
}