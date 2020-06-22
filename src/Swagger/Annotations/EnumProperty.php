<?php

namespace App\Swagger\Annotations;

use Swagger\Annotations\Property;

/**
 * @Annotation
 */
class EnumProperty extends Property
{
    private const ENUM_PROPERTY = 'enumPath';

    public string $glue = '|';

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if (self::ENUM_PROPERTY === $property) {
            $choices = $value::getValues();
            /** @psalm-suppress InvalidPropertyAssignmentValue */
            /* @phpstan-ignore-next-line */
            $this->example = implode($this->glue, $choices);
        } else {
            parent::__set($property, $value);
        }
    }
}
