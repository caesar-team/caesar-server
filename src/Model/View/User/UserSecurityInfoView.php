<?php

declare(strict_types=1);

namespace App\Model\View\User;

class UserSecurityInfoView
{
    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var array
     */
    private $permissions = [];

    public function __construct(array $roles, array $permissions)
    {
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function view(): array
    {
        return [
            'roles' => $this->roles,
            'permissions' => $this->getExistsPermissions(),
        ];
    }

    private function getExistsPermissions(): array
    {
        $keys = [];
        foreach ($this->permissions as $key => $permission) {
            if ($permission) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}