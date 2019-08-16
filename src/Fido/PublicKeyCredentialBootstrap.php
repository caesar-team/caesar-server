<?php

declare(strict_types=1);

namespace App\Fido;

use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\PublicKeyCredentialRpEntity;

final class PublicKeyCredentialBootstrap
{
    private const BYTES_LENGTH = 32;

    /**
     * @var int
     */
    private $timeout = 60000;

    /**
     * @var AuthenticationExtensionsClientInputs
     */
    private $extensions;

    private $challenge;

    public function __construct()
    {
        $this->challenge = random_bytes(self::BYTES_LENGTH);
        $this->extensions = new AuthenticationExtensionsClientInputs();
        $this->extensions->add(new AuthenticationExtension('loc', true)); // Location of the device required during the creation process
    }

    public function getRelyingPartyEntity(): PublicKeyCredentialRpEntity
    {
        $rpEntity = new PublicKeyCredentialRpEntity(
            getenv('APP_NAME')
        );

        return $rpEntity;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getExtensions(): AuthenticationExtensionsClientInputs
    {
        return $this->extensions;
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }
}