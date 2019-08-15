<?php

declare(strict_types=1);

namespace App\Controller\Api\Fido;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Repository\PublicKeyCredentialSourceRepository;
use App\Repository\UserRepository;
use Assert\Assertion;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AndroidSafetyNetAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Cose\Algorithms;
use Webauthn\PublicKeyCredentialParameters;
use Symfony\Component\Routing\Annotation\Route;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;
use Zend\Diactoros\Response\JsonResponse;

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
            'e80e96f8.ngrok.io',
            // icon path must be secured (https)
            'https://fourxxi.atlassian.net/secure/projectavatar?pid=15003&avatarId=18528&size=xxlarge'
        );

        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => 'gribanovskiy.mihail@gmail.com']);
        $userEntity = new PublicKeyCredentialUserEntity(
            $user->getUsername(),
            $user->getId()->toString(),
            $user->getUsername(),
            'https://fourxxi.atlassian.net/secure/projectavatar?pid=15003&avatarId=18528&size=xxlarge'
        );
        $challenge = random_bytes(32);
        $publicKeyCredentialParametersList = [
            new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_ES256),
            new PublicKeyCredentialParameters('public-key', Algorithms::COSE_ALGORITHM_RS256),
        ];
        $excludedPublicKeyDescriptors = [
            new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 'ABCDEFGH'),
            new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 'ABCDEFGH'),
        ];
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

        return $this->render('fido/fido_test.html.twig', ['options' => $encodedOptions]);
    }

    /**
     * @Route(path="/register", name="fido_register")
     * @param Request $request
     */
    public function register(
        Request $request,
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        SerializerInterface $serializer
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
        $coseAlgorithmManager->add(new ECDSA\ES512());
        $coseAlgorithmManager->add(new EdDSA\EdDSA());
        $coseAlgorithmManager->add(new RSA\RS1());
        $coseAlgorithmManager->add(new RSA\RS256());
        $coseAlgorithmManager->add(new RSA\RS512());

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
        $publicKeyCredentialSource = \Webauthn\PublicKeyCredentialSource::createFromPublicKeyCredential(
            $publicKeyCredential,
            $publicKeyCredentialCreationOptions->getUser()->getId()
        );


        //You can also get the PublicKeyCredentialDescriptor.
        $publicKeyCredentialDescriptor = $publicKeyCredential->getPublicKeyCredentialDescriptor();

        // Normally this condition should be true. Just make sure you received the credential data
        $attestedCredentialData = null;
        if ($response->getAttestationObject()->getAuthData()->hasAttestedCredentialData()) {
            $attestedCredentialData = $response->getAttestationObject()->getAuthData()->getAttestedCredentialData();
        }

        //You could also access to the following information.
        $response->getAttestationObject()->getAuthData()->getSignCount(); // Current counter
        $response->getAttestationObject()->getAuthData()->isUserVerified(); // Indicates if the user was verified
        $response->getAttestationObject()->getAuthData()->isUserPresent(); // Indicates if the user was present
        $response->getAttestationObject()->getAuthData()->hasExtensions(); // Extensions are available
        $response->getAttestationObject()->getAuthData()->getExtensions(); // The extensions
        $response->getAttestationObject()->getAuthData()->getReservedForFutureUse1(); //Not used at the moment
        $response->getAttestationObject()->getAuthData()->getReservedForFutureUse2(); //Not used at the moment

        return $this->render('fido/fido_response.html.twig', ['response' => $response]);
    }
}