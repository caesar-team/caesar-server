<?php

declare(strict_types=1);

namespace App\Security;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Repository\TeamRepository;
use App\Repository\UserTeamRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const DELETE_ITEM = 'delete_item';
    public const CREATE_ITEM = 'create_item';
    public const EDIT_ITEM = 'edit_item';
    public const SHOW_ITEM = 'show_item';

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
     * @throws NonUniqueResultException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
            $itemOwner = $subject->getSignedOwner();
            $userTeam = $this->findUserTeam($subject, $user);
            switch ($attribute) {
                case self::EDIT_ITEM:
                    return $this->canEditItem($subject, $user, $itemOwner, $userTeam);
                case self::CREATE_ITEM:
                case self::DELETE_ITEM:
                    return $this->canCreateItem($user, $userTeam);
                case self::SHOW_ITEM:
                    return $user === $itemOwner;
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
     * @throws NonUniqueResultException
     */
    private function findUserTeam(Item $item, User $user): ?UserTeam
    {
        $team = $this->teamRepository->findOneByDirectory($item->getParentList());
        if (is_null($team)) {
            return null;
        }

        return $this->userTeamRepository->findOneByUserAndTeam($user, $team);
    }

    private function canEditItem(Item $item, User $user, User $itemOwner, ?UserTeam $userTeam): bool
    {
        if (!$user->isFullUser()) {
            return false;
        }

        if (!is_null($userTeam) && UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole()) {
            return true;
        }

        return (AccessEnumType::TYPE_READ !== $item->getAccess()) && $user === $itemOwner;
    }

    private function canCreateItem(User $user, ?UserTeam $userTeam): bool
    {
        if (is_null($userTeam)) {
            return $user->isFullUser();
        }

        return UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole();
    }
}
