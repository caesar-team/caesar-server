<?php

declare(strict_types=1);

namespace App\Security;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InviteVoter extends Voter
{
    public const REVOKE_INVITE = 'revoke_invite';
    public const CHANGE_ACCESS = 'change_access_invite';
    public const UPDATE_INVITE = 'update_invite';

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::REVOKE_INVITE, self::CHANGE_ACCESS, self::UPDATE_INVITE])) {
            return false;
        }

        if (!$subject instanceof Item) {
            return false;
        }

        return true;
    }

    /**
     * @param string         $attribute
     * @param Item           $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (in_array($attribute, [self::REVOKE_INVITE, self::CHANGE_ACCESS])) {
            $parentItem = $subject->getOriginalItem();
            if (null === $parentItem) {
                return false;
            }

            $parentOwner = $this->userRepository->getByItem($parentItem);

            return $parentOwner === $user;
        }

        if (in_array($attribute, [self::UPDATE_INVITE])) {
            if (null === $subject->getOriginalItem()) {
                return true;
            }

            $owner = $this->userRepository->getByItem($subject);
            if ($owner === $user) {
                return AccessEnumType::TYPE_READ !== $subject->getAccess();
            }

            return false;
        }

        throw new \LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }
}
