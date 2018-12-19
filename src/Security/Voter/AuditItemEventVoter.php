<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Audit\ItemEvent;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuditItemEventVoter extends Voter
{
    public const CREATE = 'create_event';
    public const SHOW = 'show_event';

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
        return in_array($attribute, [self::SHOW, self::CREATE]) && ($subject instanceof ItemEvent || $subject instanceof Item);
    }

    /**
     * @param string         $attribute
     * @param ItemEvent|Item $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        if (self::SHOW === $attribute && $subject instanceof ItemEvent) {
            $itemOwner[] = $this->userRepository->getByItem($subject->getItem());
            if ($subject->getItem()->getOriginalItem()) {
                $itemOwner[] = $this->userRepository->getByItem($subject->getItem()->getOriginalItem());
            }

            return in_array($user, $itemOwner);
        }

        if (self::CREATE === $attribute && $subject instanceof Item) {
            $itemOwner = $this->userRepository->getByItem($subject);

            return $itemOwner === $user;
        }

        throw new \LogicException('This code should not be reached! You must update method AuditItemEventVoter::supports()');
    }
}
