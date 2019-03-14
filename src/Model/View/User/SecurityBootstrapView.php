<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SecurityBootstrapView
{
    const STATE_SKIP = 'SKIP';
    const STATE_CREATE = 'CREATE';
    const STATE_CHECK = 'CHECK';
    const STATE_CHANGE = 'CHANGE';
    const STATE_CHECK_SHARED = 'CHECK_SHARED';

    /**
     * @var string
     * @SWG\Property(example="SKIP|CREATE|CHECK")
     */
    public $twoFactorAuthState;

    /**
     * @var string
     * @SWG\Property(example="SKIP|CHANGE")
     */
    public $passwordState;

    /**
     * @var string
     * @SWG\Property(example="CREATE|SKIP|CHANGE|CHECK_SHARED")
     */
    public $masterPasswordState;

    /**
     * @var string
     * @SWG\Property(example="SKIP|CHECK")
     */
    public $sharedItemsState;
}