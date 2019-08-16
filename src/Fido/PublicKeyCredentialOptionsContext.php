<?php

declare(strict_types=1);

namespace App\Fido;

use App\Entity\User;
use App\Fido\CredentialOptionsBuilder\OptionsBuilderInterface;

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

    public function create(User $user): \JsonSerializable
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