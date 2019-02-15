<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

class AtLeastOneOfValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof AtLeastOneOf) {
            throw new ValidatorException(sprintf('The Constraint must be %s', AtLeastOneOf::class));
        }

        $filledProperties = $this->getFilledProperties($constraint->getProperties(), $object);
        if (!count($filledProperties)) {
            $properties = implode(', ', $constraint->getProperties());
            $this->context->buildViolation(sprintf('At least one of the properties must be filled (%s)', $properties))
                ->addViolation();
        }
    }

    /**
     * @param string $propertyName
     * @param mixed  $object
     * @return bool
     */
    private function isGetter(string $propertyName, $object): bool
    {
        return method_exists($object, sprintf('get%s', ucfirst($propertyName)));
    }

    private function get(string $propertyName, $object)
    {
        $getter = 'get'.ucfirst($propertyName);

        return $object->$getter();
    }

    /**
     * @param array $properties
     * @param mixed $object
     * @return array
     */
    private function getFilledProperties(array $properties, $object): array
    {
        $filledProperties = [];
        foreach ($properties as $propertyName) {
            if (!$this->isGetter($propertyName, $object)) {
                throw new ValidatorException(sprintf('The getter for %s isn\'t exists', $propertyName));
            }

            if ($value = $this->get($propertyName, $object)) {
                $filledProperties[$propertyName] = $value;
            }
        }

        return $filledProperties;
    }
}