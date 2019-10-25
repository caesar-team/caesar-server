<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Collection  extends Constraint
{
    private const CONSTRAINT_CLASS = 'constraint';
    /**
     * @var Constraint
     */
    protected $constraint;

    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->constraint = $options[self::CONSTRAINT_CLASS];
    }


    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getItemConstraint(): Constraint
    {
        return $this->constraint;
    }
}