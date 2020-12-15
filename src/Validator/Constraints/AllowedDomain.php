<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AllowedDomain extends Constraint
{
    public string $message = 'authentication.email_domain_restriction';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
