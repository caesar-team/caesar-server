<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SecurityBootstrapView
{
    public const STATE_SKIP = 'SKIP';
    public const STATE_CREATE = 'CREATE';
    public const STATE_CHECK = 'CHECK';
    public const STATE_CHANGE = 'CHANGE';

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