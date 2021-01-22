<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "delete_list",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_list",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ListVoter::DELETE'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "sort_list",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_sort_list",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ListVoter::SORT'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "edit_list",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_edit_list",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ListVoter::EDIT'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "create_item",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_create_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::CREATE'), object.getDirectory()))"
 *     )
 * )
 */
class ListView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", enum=DirectoryEnumType::AVAILABLE_TYPES)
     */
    private string $type;

    /**
     * @SWG\Property(type="string", example=0)
     */
    private int $sort;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example="{4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46, 2fcc1ae0-4fd6-5c16-6e4b-7c37486c7d46}")
     */
    private array $children;

    /**
     * @SWG\Property(type="string", example="lists")
     */
    private ?string $label;

    /**
     * @SWG\Property(type="string", example="2020-06-24T08:03:12+00:00")
     */
    private ?\DateTimeImmutable $createdAt;

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
        $this->sort = 0;
        $this->teamId = null;
        $this->createdAt = null;
        $this->label = null;
        $this->children = [];
    }

    public function getId(): string
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

    /**
     * @return string[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param string[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
