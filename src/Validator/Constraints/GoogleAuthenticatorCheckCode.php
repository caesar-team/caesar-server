<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GoogleAuthenticatorCheckCode extends Constraint
{
    public string $message = 'Invalid two-factor authentication code.';
}
