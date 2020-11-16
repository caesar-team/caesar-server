<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Swagger\Annotations as SWG;

final class ShareView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $keypairId;

    public function __construct()
    {
        $this->userId = '';
        $this->keypairId = '';
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getKeypairId(): string
    {
        return $this->keypairId;
    }

    public function setKeypairId(string $keypairId): void
    {
        $this->keypairId = $keypairId;
    }
}
