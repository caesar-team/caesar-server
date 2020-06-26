<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class UpdateItemView
{
    /**
     * @SWG\Property(type="string", example="-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 ....")
     */
    private ?string $secret;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string", example="2020-06-24T08:03:12+00:00")
     */
    private \DateTime $createdAt;

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
