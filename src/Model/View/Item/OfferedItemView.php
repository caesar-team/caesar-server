<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use Swagger\Annotations as SWG;

final class OfferedItemView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="credentials", enum=NodeEnumType::AVAILABLE_TYPES)
     */
    private string $type;

    /**
     * @SWG\Property(type="integer")
     */
    private int $sort = 0;

    /**
     * @SWG\Property(type="string", example="2020-06-24T08:03:12+00:00")
     */
    public \DateTime $lastUpdated;

    /**
     * @SWG\Property(type="string", example="d1f48d72-77d2-4994-af46-b85a8768ab80")
     */
    private string $listId;

    /**
     * @SWG\Property(type="string", example="Some secret")
     */
    private string $secret;

    /**
     * @SWG\Property(type="string", example="4a22d921-55db-4a1b-a340-9e503c5a631b")
     */
    private string $ownerId;

    /**
     * @SWG\Property(type="string", example="5011dbc9-5090-45df-b1c5-18bdb182f3d3")
     */
    private string $originalItemId;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getListId(): ?string
    {
        return $this->listId;
    }

    public function setListId(string $listId): void
    {
        $this->listId = $listId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getOriginalItemId(): ?string
    {
        return $this->originalItemId;
    }

    public function setOriginalItemId(string $originalItemId): void
    {
        $this->originalItemId = $originalItemId;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTime $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }
}
