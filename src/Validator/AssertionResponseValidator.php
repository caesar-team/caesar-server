<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use App\Repository\PublicKeyCredentialSourceRepository;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\RSA;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

final class AssertionResponseValidator
{

    /**
     * @var AuthenticatorAssertionResponseValidator
     */
    private $validator;
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

    /**
     * @var AttestationStatementSupportManager
     */
    private $attestationStatementSupportManager;

    /**
     * @var AttestationObjectLoader
     */
    private $attestationObjectLoader;

    /**
     * @var Decoder
     */
    private $decoder;

    public function __construct(PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository)
    {
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->bootstrap();
    }

    public function check($data, $publicKeyCredentialRequestOptions, User $user)
    {
        // We init the PSR7 Request object
        $symfonyRequest = Request::createFromGlobals();
        $psr7Request = (new DiactorosFactory())->createRequest($symfonyRequest);

        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($this->attestationObjectLoader, $this->decoder);

        // Load the data
        $publicKeyCredential = $publicKeyCredentialLoader->load($data);
        /** @var AuthenticatorAssertionResponse $response */
        $response = $publicKeyCredential->getResponse();

        // Check if the response is an Authenticator Assertion Response
        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Not an authenticator assertion response');
        }

        $this->validator->check(
            $publicKeyCredential->getRawId(),
            $response,
            $publicKeyCredentialRequestOptions,
            $psr7Request,
            $user ? $user->getId()->toString() : null
        );
    }

    private function bootstrap()
    {
        $coseAlgorithmManager = new Manager();
        $coseAlgorithmManager->add(new ECDSA\ES256());
        $coseAlgorithmManager->add(new RSA\RS256());

        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagObjectManager();
        $this->decoder = new Decoder($tagObjectManager, $otherObjectManager);

        $tokenBindnigHandler = new TokenBindingNotSupportedHandler();
        $extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();

        $this->attestationStatementSupportManager = new AttestationStatementSupportManager();
        $this->attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $this->attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($this->decoder));
        $this->attestationStatementSupportManager->add(new PackedAttestationStatementSupport($this->decoder, $coseAlgorithmManager));

        $this->attestationObjectLoader = new AttestationObjectLoader($this->attestationStatementSupportManager, $this->decoder);

        $this->validator = new AuthenticatorAssertionResponseValidator(
            $this->publicKeyCredentialSourceRepository,
            $this->decoder,
            $tokenBindnigHandler,
            $extensionOutputCheckerHandler,
            $coseAlgorithmManager
        );
    }
}