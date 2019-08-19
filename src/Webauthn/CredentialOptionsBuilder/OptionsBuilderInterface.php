<?php

declare(strict_types=1);


namespace App\Webauthn\CredentialOptionsBuilder;


use App\Entity\User;
use App\Webauthn\PublicKeyCredentialBootstrap;

interface OptionsBuilderInterface
{
    public function create(User $user): \JsonSerializable;
    public function canCreate(User $user): bool;
}