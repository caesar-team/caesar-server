<?php

declare(strict_types=1);

namespace App\Webauthn;

use App\Entity\User;
use App\Webauthn\CredentialOptionsBuilder\OptionsBuilderInterface;

final class PublicKeyCredentialOptionsContext
{
    /**
     * @var OptionsBuilderInterface[]
     */
    private $optionsBuilderInterfaces;

    public function __construct(OptionsBuilderInterface ...$optionsBuilderInterfaces)
    {
        $this->optionsBuilderInterfaces = $optionsBuilderInterfaces;
    }

    public function createOptions(User $user): ?\JsonSerializable
    {
        foreach ($this->optionsBuilderInterfaces as $optionsBuilder) {
            if ($optionsBuilder->canCreate($user)) {
                return $optionsBuilder->create($user);
            }

            continue;
        }

        throw new OptionsBuilderNotFoundException();
    }
}