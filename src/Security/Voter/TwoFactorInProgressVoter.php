<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TwoFactorInProgressVoter implements VoterInterface
{
    public const CHECK_KEY_NAME = '2fa';
    public const FLAG_NOT_PASSED = 'not_passed';

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!($token instanceof TwoFactorTokenInterface)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($token->getAuthenticatedToken() instanceof JWTUserToken) {
            $data = $this->jwtEncoder->decode($token->getAuthenticatedToken()->getCredentials());
            if (isset($data[self::CHECK_KEY_NAME])) {
                   return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
