<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintManager;
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
    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var JWTEncoderInterface
     */
    private $encoder;

    public function __construct(
        FingerprintManager $fingerprintManager,
        Security $security,
        JWTEncoderInterface $encoder
    )
    {
        $this->fingerprintManager = $fingerprintManager;
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
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     * @throws JWTDecodeFailureException
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        switch ($attribute) {
            case self::SKIP:
                if ($user->hasRole(User::ROLE_ANONYMOUS_USER) || !$user->isFullUser()) {
                    return true;
                }
                return  $this->isCompleteJwt($user);
            case self::CREATE:
                return !$user->isGoogleAuthenticatorEnabled();
            case self::CHECK:
                if (
                    $user->hasRole(User::ROLE_ANONYMOUS_USER)
                    || !$user->isFullUser()
                    || $this->fingerprintManager->hasValidFingerPrint($user)
                ) {
                    return false;
                }

                return true;
            default:
                return false;
        }
    }

    /**
     * @param User $user
     * @return bool
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
}