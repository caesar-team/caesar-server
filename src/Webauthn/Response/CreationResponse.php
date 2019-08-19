<?php

declare(strict_types=1);

namespace App\Webauthn\Response;

use App\Entity\User;

final class CreationResponse implements WebauthnResponseInterface
{

    /**
     * @var \JsonSerializable
     */
    private $options;

    /**
     * @var string
     */
    private $data;

    public function __construct(string $data, \JsonSerializable $options)
    {
        $this->options = $options;
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getOptions(): \JsonSerializable
    {
        return $this->options;
    }
}