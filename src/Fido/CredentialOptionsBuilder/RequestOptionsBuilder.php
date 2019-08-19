<?php

declare(strict_types=1);

namespace App\Fido\CredentialOptionsBuilder;

use App\Entity\PublicKeyCredentialSource;
use App\Entity\User;
use App\Fido\PublicKeyCredentialBootstrap;
use App\Repository\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

final class RequestOptionsBuilder implements OptionsBuilderInterface
{
    /**
     * @var PublicKeyCredentialBootstrap
     */
    private $bootstrap;
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $sourceRepository;

    public function __construct(PublicKeyCredentialBootstrap $bootstrap, PublicKeyCredentialSourceRepository $sourceRepository)
    {
        $this->bootstrap = $bootstrap;
        $this->sourceRepository = $sourceRepository;
    }

    public function create(User $user): \JsonSerializable
    {
        return new PublicKeyCredentialRequestOptions(
            $this->bootstrap->getChallenge(),
            $this->bootstrap->getTimeout(),
            null,
            $this->getDescriptors($user),
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            $this->bootstrap->getExtensions()
        );
    }

    public function canCreate(User $user): bool
    {
        return !$user->isTryingRegister();
    }

    /**
     * @param User $user
     * @return array|PublicKeyCredentialDescriptor[]
     */
    private function getDescriptors(User $user): array
    {
        $publicKeyCredential = $user->getPublicKeyCredential();
        /** @var PublicKeyCredentialSource[] $sources */
        $sources = $this->sourceRepository->findAllForUserEntity($publicKeyCredential);

        $descriptors = [];
        foreach ($sources as $source) {
            $descriptors[] = new PublicKeyCredentialDescriptor(
                $source->getType(),
                $source->getPublicKeyCredentialId(),
                $source->getTransports()
            );
        }

        return $descriptors;
    }
}