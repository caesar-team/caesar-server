<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\UserSecurityInfoView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserSecurityInfoViewFactory
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
}
