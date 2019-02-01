<?php

namespace App\Security\Authentication;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintManager;
use App\Security\Trusted\TrustedDeviceTokenStorage;
use App\Security\Voter\TwoFactorInProgressVoter;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class TwoFactorAuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, AuthenticationRequiredHandlerInterface
{
    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * @var TrustedDeviceTokenStorage
     */
    private $trustedDeviceTokenStorage;

    /**
     * @var FingerprintManager
     */
    private $fingerprintManager;

    /**
     * TwoFactorAuthenticationHandler constructor.
     *
     * @param JWTEncoderInterface       $jwtEncoder
     * @param TrustedDeviceTokenStorage $trustedDeviceTokenStorage
     * @param FingerprintManager        $fingerprintManager
     */
    public function __construct(JWTEncoderInterface $jwtEncoder, TrustedDeviceTokenStorage $trustedDeviceTokenStorage, FingerprintManager $fingerprintManager)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->trustedDeviceTokenStorage = $trustedDeviceTokenStorage;
        $this->fingerprintManager = $fingerprintManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->remove(Security::AUTHENTICATION_ERROR);
        $user = $token->getUser();
        if ($token instanceof JWTUserToken && $user instanceof User) {
            $data = $this->jwtEncoder->decode($token->getCredentials());
            unset($data[TwoFactorInProgressVoter::CHECK_KEY_NAME]);

            $this->fingerprintManager->rememberFingerprint($request->request->get('fingerprint'), $user);

            $responseData = [
                'token' => $this->jwtEncoder->encode($data),
            ];

            return new JsonResponse($responseData);
        }

        throw new \InvalidArgumentException('Expected an instance of %s, but got "%s".', JWTUserToken::class, get_class($token));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['errors' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationRequired(Request $request, TokenInterface $token): Response
    {
        return new JsonResponse([TwoFactorInProgressVoter::CHECK_KEY_NAME => TwoFactorInProgressVoter::FLAG_NOT_PASSED], Response::HTTP_UNAUTHORIZED);
    }
}
