<?php

declare(strict_types=1);


namespace App\Model\Request;


class ItemUpdateRequest
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}