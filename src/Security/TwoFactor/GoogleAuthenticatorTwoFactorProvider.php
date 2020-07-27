<?php

declare(strict_types=1);

namespace App\Security\TwoFactor;

use App\Security\Voter\TwoFactorInProgressVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorTwoFactorProvider as BaseGoogleAuthenticatorTwoFactorProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class GoogleAuthenticatorTwoFactorProvider extends BaseGoogleAuthenticatorTwoFactorProvider
{
    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        GoogleAuthenticatorInterface $authenticator,
        TwoFactorFormRendererInterface $formRenderer
    ) {
        parent::__construct($authenticator, $formRenderer);

        $this->jwtEncoder = $jwtEncoder;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();
        $token = $context->getToken();

        if ($token instanceof JWTUserToken) {
            $data = $this->jwtEncoder->decode($token->getCredentials());

            return isset($data[TwoFactorInProgressVoter::CHECK_KEY_NAME])
                && $user instanceof TwoFactorInterface
                && $user->isGoogleAuthenticatorEnabled()
                && $user->getGoogleAuthenticatorSecret()
            ;
        }

        if ($token instanceof PostAuthenticationGuardToken) {
            return $user instanceof TwoFactorInterface
                && $user->isGoogleAuthenticatorEnabled()
                && $user->getGoogleAuthenticatorSecret()
            ;
        }

        return false;
    }
}
