<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PostOwner extends Constraint
{
    public $message = 'You do not have access to this post.';
}
