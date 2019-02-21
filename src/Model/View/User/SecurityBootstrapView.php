<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SecurityBootstrapView
{
    const STATE_SKIP = 'SKIP';
    const STATE_CREATION = 'CREATION';
    const STATE_CHECK = 'CHECK';
    const STATE_CHANGE = 'CHANGE';

    /**
     * @var string
     * @SWG\Property(example="SKIP|CREATION|CHECK")
     */
    public $twoFactorAuthState;

    /**
     * @var string
     * @SWG\Property(example="SKIP|CHANGE")
     */
    public $passwordState;
}