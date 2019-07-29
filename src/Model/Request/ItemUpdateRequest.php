<?php

declare(strict_types=1);


namespace App\Model\Request;


class ItemUpdateRequest
{
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var string|null
     */
    private $secret;

    /**
     * @return string
     */
    public function getSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}