<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueSharedItem extends Constraint
{
    public $message = 'item.invite.user.already_invited';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}