<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Audit\PostEvent;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AuditPostEventVoter extends Voter
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
        return in_array($attribute, [self::SHOW, self::CREATE]) && ($subject instanceof PostEvent || $subject instanceof Post);
    }

    /**
     * @param string         $attribute
     * @param PostEvent|Post $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        if (self::SHOW === $attribute && $subject instanceof PostEvent) {
            $postOwner = $this->userRepository->getByPost($subject->getPost());

            return $postOwner === $user;
        }

        if (self::CREATE === $attribute && $subject instanceof Post) {
            $postOwner = $this->userRepository->getByPost($subject);

            return $postOwner === $user;
        }

        throw new \LogicException('This code should not be reached! You must update method AuditPostEventVoter::supports()');
    }
}
