<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * class EnumType.
 *
 * @Annotation
 */
class EnumType extends Constraint
{
    public string $message = 'Value %current_type% does not exist in possible variants (%variants%)';
    public ?string $type;
}
