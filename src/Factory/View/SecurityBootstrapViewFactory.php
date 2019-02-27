<?php

declare(strict_types=1);

namespace App\Factory\View;


use App\Entity\Fingerprint;
use App\Entity\User;
use App\Model\View\User\SecurityBootstrapView;
use App\Security\Fingerprint\FingerprintManager;

class SecurityBootstrapViewFactory
{
    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;

    public function __construct(FingerprintManager $fingerprintManager)
    {
        $this->fingerprintManager = $fingerprintManager;
    }

    public function create(User $user):SecurityBootstrapView
    {
        $securityBootstrapView = new SecurityBootstrapView();
        $securityBootstrapView->twoFactorAuthState = $this->getTwoFactorAuthState($user);
        $securityBootstrapView->passwordState = $this->getPasswordState($user);
        $securityBootstrapView->masterPasswordState = $this->getMasterPasswordState($user);

        return $securityBootstrapView;
    }

    private function getTwoFactorAuthState(User $user): string
    {
        switch (true) {
            case $user->hasRole(User::ROLE_ANONYMOUS_USER):
                $state = SecurityBootstrapView::STATE_SKIP;
                break;
            case !$user->isGoogleAuthenticatorEnabled():
                $state = SecurityBootstrapView::STATE_CREATE;
                break;
            case $this->isExpiredFingerprint($user):
                $state = SecurityBootstrapView::STATE_CHECK;
                break;
            default:
                $state = SecurityBootstrapView::STATE_SKIP;
        }

        return $state;
    }

    private function isExpiredFingerprint(User $user): bool
    {
        /** @var Fingerprint $fingerPrint */
        $fingerPrint = $this->fingerprintManager->findFingerPrintByUser($user);

        if ($fingerPrint instanceof Fingerprint) {
            return !$this->fingerprintManager->isValidDate($fingerPrint->getCreatedAt());
        }

        return true;
    }

    private function getPasswordState(User $user): string
    {
        switch (true) {
            case $user->hasRole(User::ROLE_READ_ONLY_USER):
                $state = User::FLOW_STATUS_CHANGE_PASSWORD === $user->getFlowStatus() ? SecurityBootstrapView::STATE_CHANGE : SecurityBootstrapView::STATE_SKIP;
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
                $flowStatuses = [
                    User::FLOW_STATUS_CHANGE_PASSWORD,
                    User::FLOW_STATUS_INCOMPLETE,
                ];
                $state = in_array($user->getFlowStatus(), $flowStatuses) ? SecurityBootstrapView::STATE_CHECK_SHARED : SecurityBootstrapView::STATE_CHECK;
                break;
            case $user->hasRole(User::ROLE_ANONYMOUS_USER):
                $state = SecurityBootstrapView::STATE_CHECK;
            break;
            default:
                $state = is_null($user->getEncryptedPrivateKey()) ? SecurityBootstrapView::STATE_CREATE : SecurityBootstrapView::STATE_CHECK;
                break;
        }

        return $state;
    }
}