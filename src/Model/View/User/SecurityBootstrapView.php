<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SecurityBootstrapView
{
    const STATE_SKIP = 'SKIP';
    const STATE_CREATION = 'CREATION';
    const STATE_CHECK = 'CHECK';

    /**
     * @var string
     * @SWG\Property(example="SKIP|CREATION|CHECK")
     */
    public $twoFactorAuthState;
}