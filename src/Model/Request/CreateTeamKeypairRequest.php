<?php

declare(strict_types=1);

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateTeamKeypairRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $secret;

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}
