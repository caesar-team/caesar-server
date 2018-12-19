<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ItemOwner extends Constraint
{
    public $message = 'You do not have access to this item.';
}
