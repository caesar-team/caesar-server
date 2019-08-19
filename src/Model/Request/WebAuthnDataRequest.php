<?php

declare(strict_types=1);

namespace App\Model\Request;

final class WebAuthnDataRequest
{
    /**
     * @var string
     */
    private $data = "";

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }
}