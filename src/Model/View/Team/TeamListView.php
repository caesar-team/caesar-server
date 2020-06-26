<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Model\View\Item\ItemView;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "sort_list",
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
 *     "create_item",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_create_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamListVoter::CREATE_ITEM'), object.getDirectory()))"
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
     * @SWG\Property(type="string", enum=NodeEnumType::AVAILABLE_TYPES)
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
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $children;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $teamId;

    /**
     * @Serializer\Exclude
     */
    private Directory $directory;

    public function __construct(Directory $directory)
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
     * @return ItemView[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param ItemView[] $children
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

    public function getDirectory(): Directory
    {
        return $this->directory;
    }

    public function setDirectory(Directory $directory): void
    {
        $this->directory = $directory;
    }
}
