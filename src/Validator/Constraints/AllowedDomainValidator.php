<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Security\Domain\DomainCheckerInterface;
use App\Security\Domain\Util\EmailParser;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AllowedDomainValidator extends ConstraintValidator
{
    private DomainCheckerInterface $domainChecker;

    public function __construct(DomainCheckerInterface $domainChecker)
    {
        $this->domainChecker = $domainChecker;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof AllowedDomain) {
            throw new UnexpectedTypeException($constraint, AllowedDomain::class);
        }

        if (null === $value) {
            return;
        }

        if (!$this->domainChecker->check((string) $value)) {
            $this->context
                ->buildViolation($constraint->message, ['%domain%' => EmailParser::getEmailDomain($value)])
                ->addViolation()
            ;
        }
    }
}
