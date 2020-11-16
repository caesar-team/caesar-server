<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\SecurityBootstrapView;
use App\Repository\TeamRepository;
use App\Security\AuthorizationManager\AuthorizationManager;
use App\Security\Voter\TwoFactorAuthStateVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityBootstrapViewFactory
{
    private AuthorizationManager $authorizationManager;

    private TeamRepository $teamRepository;

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        AuthorizationManager $authorizationManager,
        TeamRepository $teamRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationManager = $authorizationManager;
        $this->teamRepository = $teamRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createSingle(User $user): SecurityBootstrapView
    {
        $securityBootstrapView = new SecurityBootstrapView();
        $securityBootstrapView->setTwoFactorAuthState($this->getTwoFactorAuthState($user));
        $securityBootstrapView->setPasswordState($this->getPasswordState($user));
        $securityBootstrapView->setMasterPasswordState($this->getMasterPasswordState($user));

        return $securityBootstrapView;
    }

    private function getTwoFactorAuthState(User $user): string
    {
        if ($this->authorizationChecker->isGranted(TwoFactorAuthStateVoter::SKIP)) {
            return SecurityBootstrapView::STATE_SKIP;
        }

        if ($this->authorizationChecker->isGranted(TwoFactorAuthStateVoter::CREATE)) {
            return SecurityBootstrapView::STATE_CREATE;
        }

        if ($this->authorizationChecker->isGranted(TwoFactorAuthStateVoter::CHECK)) {
            return SecurityBootstrapView::STATE_CHECK;
        }

        return SecurityBootstrapView::STATE_SKIP;
    }

    private function getPasswordState(User $user): string
    {
        switch (true) {
            case $user->hasRole(User::ROLE_READ_ONLY_USER):
                $state = SecurityBootstrapView::STATE_SKIP;
                break;
            case $user->isFullUser() && $this->authorizationManager->hasInvitation($user):
                $state = SecurityBootstrapView::STATE_CHANGE;
                break;
            default:
                $state = SecurityBootstrapView::STATE_SKIP;
        }

        return $state;
    }

    private function getMasterPasswordState(User $user): string
    {
        switch (true) {
            case $user->hasRole(User::ROLE_READ_ONLY_USER):
            case $user->hasRole(User::ROLE_ANONYMOUS_USER):
                $state = SecurityBootstrapView::STATE_CHECK;
                break;
            case $user->isFullUser() && $this->authorizationManager->hasInvitation($user):
                $state = SecurityBootstrapView::STATE_CREATE;
                break;
            default:
                $state = is_null($user->getEncryptedPrivateKey()) ? SecurityBootstrapView::STATE_CREATE : SecurityBootstrapView::STATE_CHECK;
                break;
        }

        return $state;
    }
}
