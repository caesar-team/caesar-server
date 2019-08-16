<?php

declare(strict_types=1);

namespace App\Controller\Api\Fido;

use App\Controller\AbstractController;
use App\Entity\PublicKeyCredentialSource;
use App\Entity\User;
use App\Repository\PublicKeyCredentialSourceRepository;
use App\Repository\UserRepository;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Cose\Algorithms;
use Webauthn\PublicKeyCredentialParameters;
use Symfony\Component\Routing\Annotation\Route;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\RSA;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

/**
 * @Route(path="/api/anonymous/fido")
 */
final class FidoController extends AbstractController
{
    /**
     * @Route(path="/prepare", name="fido_prepare")
     * @param Request $request
     * @return Response
     */
    public function prepare(Request $request, UserRepository $userRepository): Response
    {
        $rpEntity = new PublicKeyCredentialRpEntity(
            getenv('APP_NAME'),
            null, // null - current domain
            // icon path must be secured (https)
            'https://fourxxi.atlassian.net/secure/projectavatar?pid=15003&avatarId=18528&size=xxlarge'
        );

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'gribanovskiy.mihail@gmail.com']);
        $userEntity = new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $user->getId()->toString(),
            $user->getUsername(),
            'https://fourxxi.atlassian.net/secure/projectavatar?pid=15003&avatarId=18528&size=xxlarge'
        );
        $challenge = random_bytes(32);
        $publicKeyCredentialParametersList = [
            new PublicKeyCredentialParameters(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, Algorithms::COSE_ALGORITHM_ES256),
            new PublicKeyCredentialParameters(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, Algorithms::COSE_ALGORITHM_RS256),
        ];
        $excludedPublicKeyDescriptors = [];
        $timeout = 20000;
        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();
        $extensions = new AuthenticationExtensionsClientInputs();
        $extensions->add(new AuthenticationExtension('loc', true));

        $publicKeyCredentialCreationOptions = new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $challenge,
            $publicKeyCredentialParametersList,
            $timeout,
            $excludedPublicKeyDescriptors,
            $authenticatorSelectionCriteria,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $extensions
        );
        $encodedOptions = json_encode($publicKeyCredentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $session = $request->getSession();
        $session->set('publicKeyCredentialCreationOptions', $encodedOptions);

        return $this->render('fido/fido_prepare.html.twig', ['options' => $encodedOptions]);
    }

    /**
     * @Route(path="/register", name="fido_register")
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function register(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository
    )
    {
        $session = $request->getSession();
        // Retrieve the PublicKeyCredentialCreationOptions object created earlier
        $publicKeyCredentialCreationOptions = $session->get('publicKeyCredentialCreationOptions');
        $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::createFromString($publicKeyCredentialCreationOptions);

        // Retrieve de data sent by the device
        $data = base64_decode($request->query->get('data'));

        // Cose Algorithm Manager
        $coseAlgorithmManager = new Manager();
        $coseAlgorithmManager->add(new ECDSA\ES256());
        $coseAlgorithmManager->add(new RSA\RS256());

        // Create a CBOR Decoder object
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagObjectManager();
        $decoder = new Decoder($tagObjectManager, $otherObjectManager);

        // The token binding handler
        $tokenBindnigHandler = new TokenBindingNotSupportedHandler();

        // Attestation Statement Support Manager
        $attestationStatementSupportManager = new AttestationStatementSupportManager();
        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($decoder));
        $attestationStatementSupportManager->add(new AndroidKeyAttestationStatementSupport($decoder));
        $attestationStatementSupportManager->add(new TPMAttestationStatementSupport());
        $attestationStatementSupportManager->add(new PackedAttestationStatementSupport($decoder, $coseAlgorithmManager));

        // Attestation Object Loader
        $attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager, $decoder);

        // Public Key Credential Loader
        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader, $decoder);

        // Extension Output Checker Handler
        $extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();

        // Authenticator Attestation Response Validator
        $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
            $attestationStatementSupportManager,
            $publicKeyCredentialSourceRepository,
            $tokenBindnigHandler,
            $extensionOutputCheckerHandler
        );

        try {
            // We init the PSR7 Request object
            $symfonyRequest = Request::createFromGlobals();
            $psr7Request = (new DiactorosFactory())->createRequest($symfonyRequest);

            // Load the data
            $publicKeyCredential = $publicKeyCredentialLoader->load($data);
            $response = $publicKeyCredential->getResponse();

            // Check if the response is an Authenticator Attestation Response
            if (!$response instanceof AuthenticatorAttestationResponse) {
                throw new \RuntimeException('Not an authenticator attestation response');
            }

            // Check the response against the request
            $authenticatorAttestationResponseValidator->check($response, $publicKeyCredentialCreationOptions, $psr7Request);
        } catch (\Throwable $exception) {
            $this->redirectToRoute('fido_prepare');
        }

        // Everything is OK here.

        // You can get the Public Key Credential Source. This object should be persisted using the Public Key Credential Source repository
        $publicKeyCredentialSource = PublicKeyCredentialSource::createFromPublicKeyCredential(
            $publicKeyCredential,
            $publicKeyCredentialCreationOptions->getUser()->getId()
        );
        $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

        return $this->render('fido/fido_register.html.twig', ['response' => $response]);
    }

    /**
     * @Route(path="/login", name="fido_login")
     * @param Request $request
     * @param PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function login(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        UserRepository $userRepository
    )
    {
        $extensions = new AuthenticationExtensionsClientInputs();
        $extensions->add(new AuthenticationExtension('loc', true));
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'gribanovskiy.mihail@gmail.com']);
        $publicKeyCredential = User::createPublicKeyCredential($user);
        /** @var PublicKeyCredentialSource[] $sources */
        $sources = $publicKeyCredentialSourceRepository->findAllForUserEntity($publicKeyCredential);

        $descriptors = [];
        foreach ($sources as $source) {
            $descriptors[] = new PublicKeyCredentialDescriptor(
                $source->getType(),
                $source->getPublicKeyCredentialId(),
                $source->getTransports()
            );
        }

        // Public Key Credential Request Options
        $publicKeyCredentialRequestOptions = new PublicKeyCredentialRequestOptions(
            random_bytes(32),                                                           // Challenge
            60000,                                                                      // Timeout
            null,                                                          // Relying Party ID
            $descriptors,                                  // Registered PublicKeyCredentialDescriptor classes
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, // User verification requirement
            $extensions
        );
        $encodedOptions = json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $session = $request->getSession();
        $session->set('publicKeyCredentialRequestOptions', $encodedOptions);

        return $this->render('fido/fido_login.html.twig', ['options' => $encodedOptions]);
    }

    /**
     * @Route(path="/login_check", name="fido_login_check")
     */
    public function loginCheck(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        UserRepository $userRepository
    )
    {
        $session = $request->getSession();
        $publicKeyCredentialRequestOptions = $session->get('publicKeyCredentialRequestOptions');
        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::createFromString($publicKeyCredentialRequestOptions);
        $data = base64_decode($request->query->get('data'));

        $coseAlgorithmManager = new Manager();
        $coseAlgorithmManager->add(new ECDSA\ES256());
        $coseAlgorithmManager->add(new RSA\RS256());

        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagObjectManager();
        $decoder = new Decoder($tagObjectManager, $otherObjectManager);

        $attestationStatementSupportManager = new AttestationStatementSupportManager();
        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($decoder));
        $attestationStatementSupportManager->add(new PackedAttestationStatementSupport($decoder, $coseAlgorithmManager));

        $attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager, $decoder);

        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader, $decoder);
        $tokenBindnigHandler = new TokenBindingNotSupportedHandler();

        $extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();

        $authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
            $publicKeyCredentialSourceRepository,
            $decoder,
            $tokenBindnigHandler,
            $extensionOutputCheckerHandler,
            $coseAlgorithmManager
        );

        $user = $userRepository->findOneBy(['email' => 'gribanovskiy.mihail@gmail.com']);

        try {
            // We init the PSR7 Request object
            $symfonyRequest = Request::createFromGlobals();
            $psr7Request = (new DiactorosFactory())->createRequest($symfonyRequest);

            // Load the data
            $publicKeyCredential = $publicKeyCredentialLoader->load($data);
            /** @var AuthenticatorAssertionResponse $response */
            $response = $publicKeyCredential->getResponse();

            // Check if the response is an Authenticator Assertion Response
            if (!$response instanceof AuthenticatorAssertionResponse) {
                throw new \RuntimeException('Not an authenticator assertion response');
            }


            // Check the response against the attestation request
            $authenticatorAssertionResponseValidator->check(
                $publicKeyCredential->getRawId(),
                $response,
                $publicKeyCredentialRequestOptions,
                $psr7Request,
                $user ? $user->getId()->toString() : null
            );

            return $this->render('fido/fido_done.html.twig');
        } catch (\Throwable $throwable) {
            dump($throwable->getTraceAsString());
            dump($throwable->getMessage()); die;
        }
    }
}