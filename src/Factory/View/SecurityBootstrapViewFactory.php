<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\SecurityBootstrapView;
use App\Repository\TeamRepository;
use App\Security\AuthorizationManager\AuthorizationManager;
use App\Security\Voter\TwoFactorAuthStateVoter;
use App\Utils\DirectoryHelper;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityBootstrapViewFactory
{
    /**
     * @var AuthorizationManager
     */
    private $authorizationManager;
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        AuthorizationManager $authorizationManager,
        TeamRepository $teamRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationManager = $authorizationManager;
        $this->teamRepository = $teamRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws JWTDecodeFailureException
     */
    public function create(User $user): SecurityBootstrapView
    {
        $securityBootstrapView = new SecurityBootstrapView();
        $securityBootstrapView->twoFactorAuthState = $this->getTwoFactorAuthState($user);
        $securityBootstrapView->passwordState = $this->getPasswordState($user);
        $securityBootstrapView->masterPasswordState = $this->getMasterPasswordState($user);
        $securityBootstrapView->sharedItemsState = $this->getSharedItemsStepState($user);

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

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getSharedItemsStepState(User $user): string
    {
        switch (true) {
            case $this->authorizationManager->hasInvitation($user) && SecurityBootstrapView::STATE_CREATE === $this->getMasterPasswordState($user):
                $state = SecurityBootstrapView::STATE_CHECK;
                break;
            case $user->isFullUser():
                $teams = $this->teamRepository->findByUser($user);
                $state = DirectoryHelper::hasOfferedItems($user, $teams) ? SecurityBootstrapView::STATE_CHECK : SecurityBootstrapView::STATE_SKIP;
                break;
            default:
                $state = SecurityBootstrapView::STATE_SKIP;
        }

        return $state;
    }
}
