<?php

namespace App\Security\Authentication;

use App\Entity\User;
use App\Security\Fingerprint\FingerprintManager;
use App\Security\Fingerprint\FingerprintStasher;
use App\Security\Voter\TwoFactorInProgressVoter;
use InvalidArgumentException;
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
     * @var FingerprintManager
     */
    private $fingerprintManager;

    /**
     * @var FingerprintStasher
     */
    private $fingerprintStasher;

    public function __construct(JWTEncoderInterface $jwtEncoder, FingerprintManager $fingerprintManager, FingerprintStasher $fingerprintStasher)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->fingerprintManager = $fingerprintManager;
        $this->fingerprintStasher = $fingerprintStasher;
    }

    /**
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     *
     * @return JsonResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->remove(Security::AUTHENTICATION_ERROR);
        $user = $token->getUser();
        if ($token instanceof JWTUserToken && $user instanceof User) {
            $data = $this->jwtEncoder->decode($token->getCredentials());
            unset($data[TwoFactorInProgressVoter::CHECK_KEY_NAME]);

            $fingerprint = $request->request->get('fingerprint');
            $response = new JsonResponse();
            if (!empty($fingerprint)) {
                $this->fingerprintManager->rememberFingerprint($request->request->get('fingerprint'), $user);
                $this->fingerprintStasher->stash($response, $request->request->get('fingerprint'));
            }

            $responseData = [
                'token' => $this->jwtEncoder->encode($data),
            ];

            return $response->setData($responseData);
        }

        throw new InvalidArgumentException('Expected an instance of %s, but got "%s".', JWTUserToken::class, get_class($token));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new AuthenticationException($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationRequired(Request $request, TokenInterface $token): Response
    {
        return new JsonResponse([TwoFactorInProgressVoter::CHECK_KEY_NAME => TwoFactorInProgressVoter::FLAG_NOT_PASSED], Response::HTTP_UNAUTHORIZED);
    }
}
