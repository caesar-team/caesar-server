<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class UserSecurityInfoView
{
    /**
     * @SWG\Property(type="string[]", example="['ROLE_USER']")
     */
    private array $roles;

    /**
     * @SWG\Property(type="string[]", example="['create', 'read', 'update', 'delete']")
     */
    private array $permissions;

    public function __construct()
    {
        $this->roles = [];
        $this->permissions = [];
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param string[] $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}
