<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\UserSecurityInfoView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserSecurityInfoViewFactory
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createSingle(User $user): UserSecurityInfoView
    {
        $view = new UserSecurityInfoView();
        $userPermissions = [
            'create' => $this->isGranted('create', $user),
            'read' => $this->isGranted('read', $user),
            'update' => $this->isGranted('update', $user),
            'delete' => $this->isGranted('delete', $user),
        ];
        $view->setRoles($user->getRoles());
        $view->setPermissions($this->getExistsPermissions($userPermissions));

        return $view;
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
