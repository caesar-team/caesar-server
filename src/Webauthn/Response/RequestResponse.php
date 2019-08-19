<?php

declare(strict_types=1);

namespace App\Webauthn\Response;

use App\Entity\User;

final class RequestResponse implements WebauthnResponseInterface
{

    /**
     * @var \JsonSerializable
     */
    private $options;

    /**
     * @var string
     */
    private $data;

    /**
     * @var User
     */
    private $user;

    public function __construct(string $data, \JsonSerializable $options, User $user)
    {
        $this->options = $options;
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getOptions(): \JsonSerializable
    {
        return $this->options;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}