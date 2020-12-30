<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\TeamDirectory;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_sort_list",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_sort_list",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::SORT'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_create_item",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_create_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::CREATE'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_edit_list",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_team_edit_list",
 *         parameters={ "team": "expr(object.getTeamId())", "list": "expr(object.getDirectoryId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::EDIT'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_delete_list",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_edit_list",
 *         parameters={ "team": "expr(object.getTeamId())", "list": "expr(object.getDirectoryId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::DELETE'), object.getDirectory()))"
 *     )
 * )
 */
final class TeamListView
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
     * @SWG\Property(type="integer", example=0)
     */
    private int $sort;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $label;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example="{4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46, 2fcc1ae0-4fd6-5c16-6e4b-7c37486c7d46}")
     */
    private array $children;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $teamId;

    /**
     * @Serializer\Exclude
     */
    private TeamDirectory $directory;

    public function __construct(TeamDirectory $directory)
    {
        $this->children = [];
        $this->sort = 0;
        $this->directory = $directory;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
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

    public function getTeamId(): ?string
    {
        return $this->teamId;
    }

    public function setTeamId(?string $teamId): void
    {
        $this->teamId = $teamId;
    }

    public function getDirectory(): TeamDirectory
    {
        return $this->directory;
    }

    public function setDirectory(TeamDirectory $directory): void
    {
        $this->directory = $directory;
    }

    public function getDirectoryId(): ?string
    {
        return $this->directory->getId()->toString();
    }
}
