<?php

declare(strict_types=1);

namespace App\Validator\Fido;

use App\Entity\PublicKeyCredentialSource;
use App\Fido\Response\CreationResponse;
use App\Fido\Response\FidoResponseInterface;
use App\Fido\ResponseValidatorBootstrap;
use App\Repository\PublicKeyCredentialSourceRepository;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;

final class AttestationResponseValidator implements ResponseValidatorInterface
{
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

    /**
     * @var PublicKeyCredential
     */
    private $publicKeyCredential;

    /**
     * @var ResponseValidatorBootstrap
     */
    private $bootstrap;

    public function __construct(
        PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository,
        ResponseValidatorBootstrap $bootstrap
    )
    {
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->bootstrap = $bootstrap;
    }

    public function check(
        FidoResponseInterface $fidoResponse
    ): void
    {
        // Public Key Credential Loader
        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($this->bootstrap->getAttestationObjectLoader(), $this->bootstrap->getDecoder());
        // Load the data
        $this->publicKeyCredential = $publicKeyCredentialLoader->load($fidoResponse->getData());
        $response = $this->publicKeyCredential->getResponse();

        // Check if the response is an Authenticator Attestation Response
        if (!$response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Not an authenticator attestation response');
        }

        $psr7Request = $this->createPsr7Request();

        $validator = $this->createValidator();

        $validator->check($response, $fidoResponse->getOptions(), $psr7Request);
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

    private function createValidator(): AuthenticatorAttestationResponseValidator
    {
        return new AuthenticatorAttestationResponseValidator(
            $this->bootstrap->getAttestationStatementSupportManager(),
            $this->publicKeyCredentialSourceRepository,
            $this->bootstrap->getTokenBindnigHandler(),
            $this->bootstrap->getExtensionOutputCheckerHandler()
        );
    }

    public function canCheck(FidoResponseInterface $response): bool
    {
        return $response instanceof CreationResponse;
    }

    private function createPsr7Request(): ServerRequestInterface
    {
        $symfonyRequest = Request::createFromGlobals();

        return (new DiactorosFactory())->createRequest($symfonyRequest);
    }
}