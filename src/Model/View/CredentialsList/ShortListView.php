<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

class ShortListView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example=0)
     */
    private int $sort;

    /**
     * @SWG\Property(type="string", enum=DirectoryEnumType::AVAILABLE_TYPES)
     */
    private string $type;

    /**
     * @SWG\Property(type="string", example="lists")
     */
    private ?string $label;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $teamId;

    /**
     * @Serializer\Exclude
     */
    private AbstractDirectory $directory;

    public function __construct(AbstractDirectory $directory)
    {
        $this->directory = $directory;
        $this->label = null;
        $this->teamId = null;
        $this->sort = 0;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    public function setTeamId(?string $teamId): void
    {
        $this->teamId = $teamId;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }
}
