<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueTeamKeypair extends Constraint
{
    public string $message = 'item.keypair.unique';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
