<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AtLeastOneOf extends Constraint
{
    protected $properties = [];
    public function __construct(array $properties)
    {
        $this->properties = $properties;
        parent::__construct($properties);
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}