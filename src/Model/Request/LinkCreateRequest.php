<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;

class LinkCreateRequest
{
    /**
     * @var Item
     */
    private $item;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $encryptedPrivateKey;

    /**
     * @var string
     */
    private $secret;

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getEncryptedPrivateKey(): ?string
    {
        return $this->encryptedPrivateKey;
    }

    public function setEncryptedPrivateKey(string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}
