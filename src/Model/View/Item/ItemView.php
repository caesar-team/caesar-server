<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Model\View\CredentialsList\ChildItemView;
use App\Model\View\CredentialsList\InviteItemView;
use App\Model\View\CredentialsList\UpdateView;
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
 *         excludeIf="expr(not is_granted(constant('App\\Security\\ItemVoter::MOVE_ITEM'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "edit_item",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_edit_item",
 *         parameters={ "id": "expr(object.getId())" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "delete_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_item",
 *         parameters={ "id": "expr(object.getId())" }
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
     * @SWG\Property(@Model(type=ChildItemView::class))
     */
    private ?ChildItemView $shared;

    /**
     * @var ChildItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ChildItemView::class))
     */
    private array $items;

    /**
     * @SWG\Property(@Model(type=UpdateView::class))
     */
    private ?UpdateView $update;

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
        $this->sort = 0;
        $this->favorite = false;
        $this->invited = [];
        $this->items = [];
        $this->tags = [];
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

    public function getShared(): ?ChildItemView
    {
        return $this->shared;
    }

    public function setShared(?ChildItemView $shared): void
    {
        $this->shared = $shared;
    }

    /**
     * @return ChildItemView[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ChildItemView[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getUpdate(): ?UpdateView
    {
        return $this->update;
    }

    public function setUpdate(?UpdateView $update): void
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
