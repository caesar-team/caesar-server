<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueEntityPropertyValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntityProperty) {
            throw new UnexpectedTypeException($constraint, UniqueEntityProperty::class);
        }

        if (!is_string($constraint->entityClass)) {
            throw new UnexpectedTypeException($constraint->entityClass, 'string');
        }

        if (!is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }

        if ('' === $value || is_null($value)) {
            return;
        }

        $entityManager = $this->managerRegistry->getManagerForClass($constraint->entityClass);

        if (null === $entityManager) {
            throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', $constraint->entityClass));
        }

        $class = $entityManager->getClassMetadata($constraint->entityClass);

        if (!$class->hasField($constraint->field) && !$class->hasAssociation($constraint->field)) {
            throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $constraint->field));
        }

        if (\is_string($value) && $constraint->lowercase) {
            $value = \mb_strtolower($value);
        }

        $repository = $entityManager->getRepository($constraint->entityClass);
        $result = $repository->{$constraint->repositoryMethod}([$constraint->field => $value]);

        if (!\is_array($result)) {
            $result = null === $result ? [] : [$result];
        }

        if (!$result) {
            return;
        }

        if (1 === \count($result) && null !== $constraint->currentEntityExpression) {
            $expressionLanguage = new ExpressionLanguage();
            $currentEntity = $expressionLanguage->evaluate($constraint->currentEntityExpression, [
                'this' => $this->context->getObject(),
            ]);

            if (!$currentEntity instanceof $constraint->entityClass) {
                throw new UnexpectedTypeException($currentEntity, $constraint->entityClass);
            }

            if (\current($result) === $currentEntity) {
                return;
            }
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation()
        ;
    }
}
