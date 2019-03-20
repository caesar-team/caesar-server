<?php

declare(strict_types=1);

namespace App\Factory\View;


use App\Entity\Directory;
use App\Entity\Fingerprint;
use App\Entity\Item;
use App\Entity\User;
use App\Model\View\User\SecurityBootstrapView;
use App\Repository\ItemRepository;
use App\Security\AuthorizationManager\AuthorizationManager;
use App\Security\Fingerprint\FingerprintManager;
use App\Security\Voter\TwoFactorInProgressVoter;
use App\Utils\DirectoryHelper;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Security;

class SecurityBootstrapViewFactory
{
    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var JWTEncoderInterface
     */
    private $encoder;
    /**
     * @var AuthorizationManager
     */
    private $authorizationManager;

    public function __construct(
        FingerprintManager $fingerprintManager,
        Security $security,
        JWTEncoderInterface $encoder,
        AuthorizationManager $authorizationManager
    )
    {
        $this->fingerprintManager = $fingerprintManager;
        $this->security = $security;
        $this->encoder = $encoder;
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * @param User $user
     * @return SecurityBootstrapView
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function create(User $user):SecurityBootstrapView
    {
        $securityBootstrapView = new SecurityBootstrapView();
        $securityBootstrapView->twoFactorAuthState = $this->getTwoFactorAuthState($user);
        $securityBootstrapView->passwordState = $this->getPasswordState($user);
        $securityBootstrapView->masterPasswordState = $this->getMasterPasswordState($user);
        $securityBootstrapView->sharedItemsState = $this->getSharedItemsStepState($user);

        return $securityBootstrapView;
    }

    /**
     * @param User $user
     * @return string
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    private function getTwoFactorAuthState(User $user): string
    {
        $isCompleteJwt = $this->isCompleteJwt($user);
        switch (true) {
            case $isCompleteJwt:
                $state = SecurityBootstrapView::STATE_SKIP;
                break;
            case !$user->isFullUser():
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

    /**
     * @param User $user
     * @return string
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
     * @param User $user
     * @return string
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
                $state = SecurityBootstrapView::STATE_CREATE ;
                break;
            default:
                $state = is_null($user->getEncryptedPrivateKey()) ? SecurityBootstrapView::STATE_CREATE : SecurityBootstrapView::STATE_CHECK;
                break;
        }

        return $state;
    }

    /**
     * @param User $user
     * @return bool
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    private function isCompleteJwt(User $user): bool
    {
        if ($this->security->getToken() instanceof JWTUserToken) {
            $decodedToken = $this->encoder->decode($this->security->getToken()->getCredentials());

            $isCompleteFlow = User::FLOW_STATUS_FINISHED === $user->getFlowStatus();
            return $isCompleteFlow && !isset($decodedToken[TwoFactorInProgressVoter::CHECK_KEY_NAME]);
        }

        return false;
    }

    /**
     * @param User $user
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getSharedItemsStepState(User $user): string
    {

        switch (true) {
            case $this->authorizationManager->hasInvitation($user) && SecurityBootstrapView::STATE_CREATE === $this->getMasterPasswordState($user):
                $state = SecurityBootstrapView::STATE_CHECK;
                break;
            case $user->isFullUser():
                $state = DirectoryHelper::hasOfferedItems($user) ? SecurityBootstrapView::STATE_CHECK : SecurityBootstrapView::STATE_SKIP;
                break;
            default:
                $state = SecurityBootstrapView::STATE_SKIP;
        }

        return $state;
    }
}