<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintCheckerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class TwoFactorAuthStateVoter extends Voter
{
    public const CREATE = 'two_factor_auth_state_create';
    public const SKIP = 'two_factor_auth_state_skip';
    public const CHECK = 'two_factor_auth_state_check';

    private const STATES = [
        self::SKIP,
        self::CREATE,
        self::CHECK,
    ];

    private FingerprintCheckerInterface $fingerprintChecker;

    private Security $security;

    private JWTEncoderInterface $encoder;

    public function __construct(
        FingerprintCheckerInterface $fingerprintChecker,
        Security $security,
        JWTEncoderInterface $encoder
    ) {
        $this->fingerprintChecker = $fingerprintChecker;
        $this->security = $security;
        $this->encoder = $encoder;
    }

    protected function supports($attribute, $subject)
    {
        if (in_array($attribute, self::STATES)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @throws JWTDecodeFailureException
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::SKIP:
                return $this->canSkip($user);
            case self::CREATE:
                return !$user->isGoogleAuthenticatorEnabled();
            case self::CHECK:
                return $this->canCheck($user);
            default:
                return false;
        }
    }

    /**
     * @throws JWTDecodeFailureException
     */
    private function isCompleteJwt(User $user): bool
    {
        if ($this->security->getToken() instanceof JWTUserToken) {
            $decodedToken = $this->encoder->decode($this->security->getToken()->getCredentials());

            $isCompleteFlow = User::FLOW_STATUS_FINISHED === $user->getFlowStatus();

            return $isCompleteFlow && !isset($decodedToken[TwoFactorInProgressVoter::CHECK_KEY_NAME]);
        }

        return false;
    }

    private function canSkip(User $user): bool
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER) || !$user->isFullUser()) {
            return true;
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            return false;
        }

        return $this->isCompleteJwt($user);
    }

    private function canCheck(User $user): bool
    {
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)
            || !$user->isFullUser()
            || $this->fingerprintChecker->hasValidFingerprint($user)
        ) {
            return false;
        }

        return true;
    }
}
