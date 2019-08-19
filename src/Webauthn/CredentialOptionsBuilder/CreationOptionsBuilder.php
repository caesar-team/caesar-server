<?php

declare(strict_types=1);

namespace App\Webauthn\CredentialOptionsBuilder;

use App\Entity\User;
use App\Webauthn\PublicKeyCredentialBootstrap;
use Cose\Algorithms;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;

final class CreationOptionsBuilder implements OptionsBuilderInterface
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var array|PublicKeyCredentialParameters[]
     */
    private $publicKeyCredentialParametersList = [];

    /**
     * @var array|PublicKeyCredentialDescriptor[]
     */
    private $excludedPublicKeyDescriptors = [];

    /**
     * @var PublicKeyCredentialBootstrap
     */
    private $bootstrap;

    public function __construct(PublicKeyCredentialBootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        $this->publicKeyCredentialParametersList = [
            new PublicKeyCredentialParameters(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, Algorithms::COSE_ALGORITHM_ES256),
            new PublicKeyCredentialParameters(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, Algorithms::COSE_ALGORITHM_RS256),
        ];
    }

    /**
     * @return array|PublicKeyCredentialParameters[]
     */
    public function getPublicKeyCredentialParametersList()
    {
        return $this->publicKeyCredentialParametersList;
    }

    /**
     * @return array|PublicKeyCredentialDescriptor[]
     */
    public function getExcludedPublicKeyDescriptors()
    {
        return $this->excludedPublicKeyDescriptors;
    }

    public function canCreate(User $user): bool
    {
        return $user->isTryingRegister();
    }

    public function create(User $user): \JsonSerializable
    {
        return new PublicKeyCredentialCreationOptions(
            $this->bootstrap->getRelyingPartyEntity(),
            $user->getPublicKeyCredential(),
            $this->bootstrap->getChallenge(),
            $this->publicKeyCredentialParametersList,
            $this->bootstrap->getTimeout(),
            $this->getExcludedPublicKeyDescriptors(),
            new AuthenticatorSelectionCriteria(),
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $this->bootstrap->getExtensions()
        );
    }
}