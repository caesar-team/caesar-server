<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class UserSecurityInfoView
{
    /**
     * @var string[]
     * @SWG\Property(example="['ROLE_USER']")
     */
    public $roles = [];

    /**
     * @var string[]
     * @SWG\Property(example="['create', 'read', 'update', 'delete']")
     */
    public $permissions = [];
}
