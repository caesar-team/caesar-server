<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class UserSecurityInfoView
{
    const SKIP = 'SKIP';
    const CREATION = 'CREATION';
    const CHECK = 'CHECK';
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

    /**
     * @var string
     * @SWG\Property(example="SKIP|CREATION|CHECK")
     */
    public $twoFactorAuthState;
}