<?php

declare(strict_types=1);

namespace App\Request\Item;

use App\Entity\Item;
use App\Entity\User;
use App\Model\AwareOwnerAndRelatedItemInterface;
use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AppAssert\UniquePersonalKeypair
 */
final class ShareItemRequest implements AwareOwnerAndRelatedItemInterface
{
    /**
     * @var User|null
     *
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $secret;

    private ?Item $item;

    public function __construct(?Item $item = null)
    {
        $this->item = $item;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getOwner(): ?User
    {
        return $this->user;
    }

    public function getRelatedItem(): ?Item
    {
        return $this->item;
    }
}
