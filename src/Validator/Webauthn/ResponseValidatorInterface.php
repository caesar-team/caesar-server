<?php

declare(strict_types=1);

namespace App\Validator\Webauthn;

use App\Webauthn\Response\WebauthnResponseInterface;

interface ResponseValidatorInterface
{
    public function canCheck(WebauthnResponseInterface $response): bool;
    public function check(WebauthnResponseInterface $response): void;
}