<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Fingerprint;
use App\Entity\User;
use App\Model\View\User\UserSecurityInfoView;
use App\Security\Fingerprint\FingerprintManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserSecurityInfoViewFactory
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, FingerprintManager $fingerprintManager)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->fingerprintManager = $fingerprintManager;
    }

    public function create(User $user): UserSecurityInfoView
    {
        $userSecurityUnfoView = new UserSecurityInfoView();
        $userPermissions = [
            'create' => $this->isGranted('create', $user),
            'read' => $this->isGranted('read', $user),
            'update' => $this->isGranted('update', $user),
            'delete' => $this->isGranted('delete', $user),
        ];
        $userSecurityUnfoView->roles = $user->getRoles();
        $userSecurityUnfoView->permissions = $this->getExistsPermissions($userPermissions);
        $twoFactorAuthState = $this->getTwoFactorAuthState($user);
        $userSecurityUnfoView->twoFactorAuthState = $twoFactorAuthState;

        return $userSecurityUnfoView;
    }

    private function isGranted($attributes, $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $subject);
    }

    private function getExistsPermissions(array $permissions): array
    {
        $keys = [];
        foreach ($permissions as $key => $permission) {
            if ($permission) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private function getTwoFactorAuthState(User $user): string
    {
        switch (true) {
            case $user->hasRole(User::ROLE_ANONYMOUS_USER):
                $state = UserSecurityInfoView::SKIP;
                break;
            case !$user->isGoogleAuthenticatorEnabled():
                $state = UserSecurityInfoView::CREATION;
                break;
            case $this->isExpiredFingerprint($user):
                $state = UserSecurityInfoView::CHECK;
                break;
            default:
                $state = UserSecurityInfoView::SKIP;
        }

        return $state;
    }

    private function isExpiredFingerprint(User $user): bool
    {
        /** @var Fingerprint $fingerPrint */
        $fingerPrint = $this->fingerprintManager->findFingerPrintByUser($user);

        return !$this->fingerprintManager->isValidDate($fingerPrint->getCreatedAt());
    }
}