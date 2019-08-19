<?php

declare(strict_types=1);

namespace App\Validator\Fido;

use App\Fido\Response\FidoResponseInterface;
use App\Fido\Response\RequestResponse;
use App\Fido\ResponseValidatorBootstrap;
use App\Repository\PublicKeyCredentialSourceRepository;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;

final class AssertionResponseValidator implements ResponseValidatorInterface
{
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

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

    /**
     * @param FidoResponseInterface|RequestResponse $fidoResponse
     */
    public function check(FidoResponseInterface $fidoResponse): void
    {
        // We init the PSR7 Request object
        $psr7Request = $this->createPsr7Request();

        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($this->bootstrap->getAttestationObjectLoader(), $this->bootstrap->getDecoder());
        // Load the data
        $publicKeyCredential = $publicKeyCredentialLoader->load($fidoResponse->getData());
        /** @var AuthenticatorAssertionResponse $response */
        $response = $publicKeyCredential->getResponse();

        // Check if the response is an Authenticator Assertion Response
        if (!$response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Not an authenticator assertion response');
        }

        $validator = $this->createValidator();

        $validator->check(
            $publicKeyCredential->getRawId(),
            $response,
            $fidoResponse->getOptions(),
            $psr7Request,
            $fidoResponse->getUser() ? $fidoResponse->getUser()->getId()->toString() : null
        );
    }

    public function canCheck(FidoResponseInterface $response): bool
    {
        return $response instanceof RequestResponse;
    }

    private function createValidator(): AuthenticatorAssertionResponseValidator
    {
        return new AuthenticatorAssertionResponseValidator(
            $this->publicKeyCredentialSourceRepository,
            $this->bootstrap->getDecoder(),
            $this->bootstrap->getTokenBindnigHandler(),
            $this->bootstrap->getExtensionOutputCheckerHandler(),
            $this->bootstrap->getCoseAlgorithmManager()
        );
    }

    private function createPsr7Request(): ServerRequestInterface
    {
        $symfonyRequest = Request::createFromGlobals();

        return (new DiactorosFactory())->createRequest($symfonyRequest);
    }
}