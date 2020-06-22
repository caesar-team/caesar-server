<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GoogleAuthenticatorCheckCodeValidator extends ConstraintValidator
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var GoogleAuthenticatorInterface
     */
    private $authenticator;

    public function __construct(Security $security, GoogleAuthenticatorInterface $authenticator)
    {
        $this->security = $security;
        $this->authenticator = $authenticator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $user = $this->security->getUser();
        if (!$constraint instanceof GoogleAuthenticatorCheckCode) {
            throw new UnexpectedTypeException($constraint, GoogleAuthenticatorCheckCode::class);
        }
        if (!$user instanceof TwoFactorInterface) {
            return;
        }
        if (!is_scalar($value)) {
            return;
        }

        if (!$this->authenticator->checkCode($user, (string) $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
