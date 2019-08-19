<?php

declare(strict_types=1);

namespace App\Fido;

use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\RSA;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

final class ResponseValidatorBootstrap
{
    /**
     * @var Decoder
     */
    private $decoder;
    /**
     * @var Manager
     */
    private $coseAlgorithmManager;
    /**
     * @var AttestationStatementSupportManager
     */
    private $attestationStatementSupportManager;

    public function __construct()
    {
        $otherObjectManager = new OtherObjectManager();
        $tagObjectManager = new TagObjectManager();

        $this->decoder = new Decoder($tagObjectManager, $otherObjectManager);
        $this->coseAlgorithmManager = new Manager();
        $this->coseAlgorithmManager->add(new ECDSA\ES256());
        $this->coseAlgorithmManager->add(new RSA\RS256());
    }


    public function getAttestationObjectLoader(): AttestationObjectLoader
    {
        $this->attestationStatementSupportManager = new AttestationStatementSupportManager();
        $this->attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $this->attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($this->decoder));
        $this->attestationStatementSupportManager->add(new PackedAttestationStatementSupport($this->decoder, $this->coseAlgorithmManager));

        return $this->attestationObjectLoader = new AttestationObjectLoader($this->attestationStatementSupportManager, $this->decoder);
    }

    public function getTokenBindnigHandler(): TokenBindingNotSupportedHandler
    {
        return new TokenBindingNotSupportedHandler();
    }

    public function getExtensionOutputCheckerHandler(): ExtensionOutputCheckerHandler
    {
        return new ExtensionOutputCheckerHandler();
    }

    public function getDecoder(): Decoder
    {
        return $this->decoder;
    }

    public function getCoseAlgorithmManager(): Manager
    {
        return $this->coseAlgorithmManager;
    }

    public function getAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        return $this->attestationStatementSupportManager;
    }
}