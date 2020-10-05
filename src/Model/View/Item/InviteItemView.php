<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\DBAL\Types\Enum\AccessEnumType;
use Swagger\Annotations as SWG;

final class InviteItemView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $userId;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $userAvatar;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $userName;

    /**
     * @SWG\Property(type="string")
     */
    private string $userEmail;

    /**
     * @SWG\Property(type="string", enum=AccessEnumType::AVAILABLE_TYPES)
     */
    private string $access;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getAccess(): string
    {
        return $this->access;
    }

    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    public function getUserAvatar(): ?string
    {
        return $this->userAvatar;
    }

    public function setUserAvatar(?string $userAvatar): void
    {
        $this->userAvatar = $userAvatar;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): void
    {
        $this->userName = $userName;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): void
    {
        $this->userEmail = $userEmail;
    }
}
