<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(EntityManagerInterface $manager)
    {
        $this->userRepository = $manager->getRepository(User::class);
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
        if (!in_array($attribute, [self::DELETE_ITEM, self::CREATE_ITEM, self::SHOW_ITEM, self::EDIT_ITEM])) {
            return false;
        }

        if (!$subject instanceof Item) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute
     * @param mixed          $subject
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
