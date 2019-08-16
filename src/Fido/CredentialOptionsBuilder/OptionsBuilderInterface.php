<?php

declare(strict_types=1);


namespace App\Fido\CredentialOptionsBuilder;


use App\Entity\User;
use App\Fido\PublicKeyCredentialBootstrap;

interface OptionsBuilderInterface
{
    public function create(User $user): \JsonSerializable;
    public function canCreate(User $user): bool;
    public function __construct(PublicKeyCredentialBootstrap $bootstrap);
}