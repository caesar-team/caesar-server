<?php

declare(strict_types=1);

namespace App\Controller\Api\Webauthn;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\Validator\WebauthnResponseValidatorFactory;
use App\Webauthn\PublicKeyCredentialOptionsContext;
use App\Webauthn\Response\CreationResponse;
use App\Webauthn\Response\RequestResponse;
use App\Form\Request\WebAuthnDataType;
use App\Model\Request\WebAuthnDataRequest;
use App\Repository\PublicKeyCredentialSourceRepository;
use App\Security\Authentication\TwoFactorAuthenticationHandler;
use App\Validator\Webauthn\AttestationResponseValidator;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Provider\JWTProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @Route(path="/api/webauthn")
 */
final class WebauthnController extends AbstractController
{
    private const SESSION_CREDENTIAL_CREATION_OPTIONS = 'publicKeyCredentialCreationOptions';
    private const SESSION_CREDENTIAL_REQUEST_OPTIONS = 'publicKeyCredentialRequestOptions';

    /**
     * @SWG\Tag(name="Webauthn")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Webauthn creation response",
     *     @Model(type="\Webauthn\PublicKeyCredentialCreationOptions")
     * )
     *
     * @Route(path="/register", name="webauthn_create", methods={"GET"})
     * @param Request $request
     * @param PublicKeyCredentialOptionsContext $credentialOptionsContext
     * @return string
     */
    public function register(
        Request $request,
        PublicKeyCredentialOptionsContext $credentialOptionsContext
    )
    {
        $session = $request->getSession();
        $session->set(self::SESSION_CREDENTIAL_CREATION_OPTIONS, null);
        /** @var User $user */
        $user = $this->getUser();

        $user->setIsTryingRegister(true);
        $credentialCreationOptions = $credentialOptionsContext->createOptions($user);

        $encodedOptions = json_encode($credentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $session->set(self::SESSION_CREDENTIAL_CREATION_OPTIONS, $encodedOptions);

        return new JsonResponse($credentialCreationOptions);
    }

    /**
     * @SWG\Tag(name="Webauthn")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\WebAuthnDataType::class)
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Register success"
     * )
     *
     * @Route(path="/register_check", name="webauthn_register", methods={"POST"})
     * @param Request $request
     *
     * @return \Symfony\Component\Form\FormInterface|JsonResponse
     * @throws \Exception
     */
    public function registerCheck(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        WebauthnResponseValidatorFactory $validatorFactory
    )
    {
        $session = $request->getSession();
        // Retrieve the PublicKeyCredentialCreationOptions object created earlier
        $publicKeyCredentialCreationOptions = $session->get(self::SESSION_CREDENTIAL_CREATION_OPTIONS);
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString($publicKeyCredentialCreationOptions);

        $data = new WebAuthnDataRequest();
        $form = $this->createForm(WebAuthnDataType::class, $data);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $data = base64_decode($data->getData());

        try {
            $response = new CreationResponse($data, $publicKeyCredentialCreationOptions);
            $validator = $validatorFactory->check($response);

            if ($validator instanceof AttestationResponseValidator) {
                $publicKeyCredentialSource = $validator->getVerifiedPublicKeyCredentialSource($publicKeyCredentialCreationOptions);
                $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);
            }
        } catch (\Throwable $exception) {
            return new JsonResponse(['success' => false]);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @SWG\Tag(name="Webauthn")
     * @SWG\Response(
     *     response=200,
     *     description="Webauthn request response",
     *     @Model(type="\Webauthn\PublicKeyCredentialRequestOptions")
     * )
     *
     * @Route(path="/login", name="webauthn_login_prepare", methods={"GET"})
     * @param Request $request
     * @param PublicKeyCredentialOptionsContext $credentialOptionsContext
     * @return Response
     */
    public function login(
        Request $request,
        PublicKeyCredentialOptionsContext $credentialOptionsContext
    )
    {
        $session = $request->getSession();
        $session->set(self::SESSION_CREDENTIAL_REQUEST_OPTIONS, null);

        /** @var User $user */
        $user = $this->getUser();

        // Public Key Credential Request Options
        $publicKeyCredentialRequestOptions = $credentialOptionsContext->createOptions($user);
        $encodedOptions = json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $session->set(self::SESSION_CREDENTIAL_REQUEST_OPTIONS, $encodedOptions);

        return new JsonResponse($publicKeyCredentialRequestOptions);
    }

    /**
     * @SWG\Tag(name="Webauthn")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\WebAuthnDataType::class)
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Login success"
     * )
     *
     * @Route(path="/login_check", name="webauthn_login_check", methods={"POST"})
     * @param Request $request
     * @param WebauthnResponseValidatorFactory $validatorFactory
     * @param TwoFactorAuthenticationHandler $authenticationHandler
     * @param JWTTokenAuthenticator $JWTTokenManager
     * @return \Symfony\Component\Form\FormInterface|JsonResponse|Response
     */
    public function loginCheck(
        Request $request,
        WebauthnResponseValidatorFactory $validatorFactory,
        TwoFactorAuthenticationHandler $authenticationHandler,
        JWTTokenAuthenticator $JWTTokenManager
    )
    {
        $session = $request->getSession();
        $publicKeyCredentialRequestOptions = $session->get(self::SESSION_CREDENTIAL_REQUEST_OPTIONS);

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($publicKeyCredentialRequestOptions);

        $data = new WebAuthnDataRequest();
        $form = $this->createForm(WebAuthnDataType::class, $data);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $data = base64_decode($data->getData());

        /** @var User $user */
        $user = $this->getUser();

        try {
            $response = new RequestResponse($data, $publicKeyCredentialRequestOptions, $user);
            $validatorFactory->check($response);
            $token =$JWTTokenManager->createAuthenticatedToken($user, 'api');

            return $authenticationHandler->onAuthenticationSuccess($request, $token);
        } catch (\Throwable $throwable) {
            throw new AccessDeniedHttpException();
        }
    }
}