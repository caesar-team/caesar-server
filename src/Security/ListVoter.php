<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Directory;
use App\Entity\User;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ListVoter extends Voter
{
    public const SHOW_ITEMS = 'list_show_items';
    public const EDIT = 'edit_list';
    public const DELETE_LIST = 'delete_list';

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::SHOW_ITEMS, self::EDIT, self::DELETE_LIST])) {
            return false;
        }

        if (!$subject instanceof Directory) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        if (in_array($attribute, [self::SHOW_ITEMS, self::EDIT, self::DELETE_LIST])) {
            $itemOwner = $this->userRepository->getByList($subject);

            return $itemOwner === $user;
        }

        throw new LogicException('This code should not be reached! You must update method UserVoter::supports()');
    }
}
