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
     * @SWG\Property(type="string", example="SKIP|CREATE|CHECK")
     */
    private string $twoFactorAuthState;

    /**
     * @SWG\Property(type="string", example="SKIP|CHANGE")
     */
    private string $passwordState;

    /**
     * @SWG\Property(type="string", example="CREATE|SKIP|CHANGE|CHECK_SHARED")
     */
    private string $masterPasswordState;

    public function getTwoFactorAuthState(): string
    {
        return $this->twoFactorAuthState;
    }

    public function setTwoFactorAuthState(string $twoFactorAuthState): void
    {
        $this->twoFactorAuthState = $twoFactorAuthState;
    }

    public function getPasswordState(): string
    {
        return $this->passwordState;
    }

    public function setPasswordState(string $passwordState): void
    {
        $this->passwordState = $passwordState;
    }

    public function getMasterPasswordState(): string
    {
        return $this->masterPasswordState;
    }

    public function setMasterPasswordState(string $masterPasswordState): void
    {
        $this->masterPasswordState = $masterPasswordState;
    }
}
