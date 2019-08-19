<?php

declare(strict_types=1);

namespace App\Validator\Fido;

use App\Entity\PublicKeyCredentialSource;
use App\Fido\Response\CreationResponse;
use App\Fido\Response\FidoResponseInterface;
use App\Repository\PublicKeyCredentialSourceRepository;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\RSA;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

final class AttestationResponseValidator implements ResponseValidatorInterface
{
    /**
     * @var AuthenticatorAttestationResponseValidator
     */
    private $validator;

    /**
     * @var AttestationStatementSupportManager
     */
    private $attestationStatementSupportManager;

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

    /**
     * @var AttestationObjectLoader
     */
    private $attestationObjectLoader;

    /**
     * @var PublicKeyCredential
     */
    private $publicKeyCredential;

    /**
     * @var Decoder
     */
    private $decoder;

    public function __construct(PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository)
    {
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->bootstrap();
    }

    public function check(
        FidoResponseInterface $fidoResponse
    ): void
    {
        // Public Key Credential Loader
        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($this->attestationObjectLoader, $this->decoder);
        // Load the data
        $this->publicKeyCredential = $publicKeyCredentialLoader->load($fidoResponse->getData());
        $response = $this->publicKeyCredential->getResponse();

        // Check if the response is an Authenticator Attestation Response
        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Not an authenticator attestation response');
        }

        $symfonyRequest = Request::createFromGlobals();
        $psr7Request = (new DiactorosFactory())->createRequest($symfonyRequest);
        $this->validator->check($response, $fidoResponse->getOptions(), $psr7Request);
    }

    /**
     * @param PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions
     * @return \Webauthn\PublicKeyCredentialSource|PublicKeyCredentialSource
     * @throws \Exception
     */
    public function getVerifiedPublicKeyCredentialSource(
        PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions
    )
    {
        return PublicKeyCredentialSource::createFromPublicKeyCredential(
            $this->publicKeyCredential,
            $publicKeyCredentialCreationOptions->getUser()->getId()
        );
    }

    private function bootstrap(): void
    {
        // Cose Algorithm Manager
        $coseAlgorithmManager = new Manager();
        $coseAlgorithmManager->add(new ECDSA\ES256());
        $coseAlgorithmManager->add(new RSA\RS256());

        // Create a CBOR Decoder object
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagObjectManager();
        $this->decoder = new Decoder($tagObjectManager, $otherObjectManager);

        // Attestation Statement Support Manager
        $this->attestationStatementSupportManager = new AttestationStatementSupportManager();
        $this->attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $this->attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($this->decoder));
        $this->attestationStatementSupportManager->add(new AndroidKeyAttestationStatementSupport($this->decoder));
        $this->attestationStatementSupportManager->add(new TPMAttestationStatementSupport());
        $this->attestationStatementSupportManager->add(new PackedAttestationStatementSupport($this->decoder, $coseAlgorithmManager));

        // Attestation Object Loader
        $this->attestationObjectLoader = new AttestationObjectLoader($this->attestationStatementSupportManager, $this->decoder);

        // The token binding handler
        $tokenBindnigHandler = new TokenBindingNotSupportedHandler();

        // Extension Output Checker Handler
        $extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();

        // Authenticator Attestation Response Validator
        $this->validator = new AuthenticatorAttestationResponseValidator(
            $this->attestationStatementSupportManager,
            $this->publicKeyCredentialSourceRepository,
            $tokenBindnigHandler,
            $extensionOutputCheckerHandler
        );
    }

    public function canCheck(FidoResponseInterface $response): bool
    {
        return $response instanceof CreationResponse;
    }
}