<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    public const DELETE_ITEM = 'delete_item';
    public const CREATE_ITEM = 'create_item';
    public const EDIT_ITEM = 'edit_item';
    public const SHOW_ITEM = 'show_item';

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
        if (!in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
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

        if (in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
            $itemOwner = $this->userRepository->getByItem($subject);

            return $itemOwner === $user;
        }

        throw new \LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }
}
