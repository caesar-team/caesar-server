<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "move_item",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_move_item",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::MOVE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "edit_item",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_edit_item",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::EDIT'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "delete_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_item",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::DELETE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "favorite_item_toggle",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_favorite_item_toggle",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::FAVORITE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "batch_share_item",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_batch_share_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ItemVoter::SHARE'), object.getItem()))"
 *     )
 * )
 *
 * @Hateoas\Relation(
 *     "team_edit_item",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_edit_item",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::EDIT'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_delete_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_item",
 *         parameters={ "id": "expr(object.getOriginalId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::DELETE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_move_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_item",
 *         parameters={ "id": "expr(object.getOriginalId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::MOVE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_batch_share_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route("api_batch_share_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::SHARE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_favorite_item_toggle",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_favorite_item_toggle",
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::FAVORITE'), object.getItem()))"
 *     )
 * )
 */
final class ItemView
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
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $listId;

    /**
     * @SWG\Property(type="string", example="-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 ....")
     */
    private ?string $secret;

    /**
     * @var InviteItemView[]
     *
     * @SWG\Property(type="array", @Model(type=InviteItemView::class))
     */
    private array $invited;

    /**
     * @SWG\Property(@Model(type=SharedChildItemView::class))
     */
    private ?SharedChildItemView $shared;

    /**
     * @SWG\Property(@Model(type=UpdateItemView::class))
     */
    private ?UpdateItemView $update;

    /**
     * @SWG\Property(type="string", example="2020-06-24T08:03:12+00:00")
     */
    private \DateTime $lastUpdated;

    /**
     * @SWG\Property(type="string[]", example={"tag"})
     */
    private array $tags;

    /**
     * @SWG\Property(type="boolean", example=false)
     */
    private bool $favorite;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $ownerId;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $originalItemId;

    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private ?string $previousListId;

    /**
     * @Serializer\Exclude()
     *
     * @SWG\Property(type="string")
     */
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
        $this->update = null;
        $this->shared = null;
        $this->sort = 0;
        $this->favorite = false;
        $this->invited = [];
        $this->tags = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOriginalId(): string
    {
        return $this->originalItemId ?: $this->id;
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

    public function getListId(): ?string
    {
        return $this->listId;
    }

    public function setListId(?string $listId): void
    {
        $this->listId = $listId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return InviteItemView[]
     */
    public function getInvited(): array
    {
        return $this->invited;
    }

    /**
     * @param InviteItemView[] $invited
     */
    public function setInvited(array $invited): void
    {
        $this->invited = $invited;
    }

    public function getShared(): ?SharedChildItemView
    {
        return $this->shared;
    }

    public function setShared(?SharedChildItemView $shared): void
    {
        $this->shared = $shared;
    }

    public function getUpdate(): ?UpdateItemView
    {
        return $this->update;
    }

    public function setUpdate(?UpdateItemView $update): void
    {
        $this->update = $update;
    }

    public function getLastUpdated(): \DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTime $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): void
    {
        $this->favorite = $favorite;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getOriginalItemId(): ?string
    {
        return $this->originalItemId;
    }

    public function setOriginalItemId(?string $originalItemId): void
    {
        $this->originalItemId = $originalItemId;
    }

    public function getPreviousListId(): ?string
    {
        return $this->previousListId;
    }

    public function setPreviousListId(?string $previousListId): void
    {
        $this->previousListId = $previousListId;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
