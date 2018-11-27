<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     * @param string                                  $value
     * @param Constraint|GoogleAuthenticatorCheckCode $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = $this->security->getUser();
        if (!$user instanceof TwoFactorInterface) {
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
