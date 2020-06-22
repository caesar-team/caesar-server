<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Entity\Item;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Hateoas\Relation(
 *     "move_item",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_move_item",
 *         parameters={ "id": "expr(object.id)" }
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
 *         parameters={ "id": "expr(object.id)" }
 *     )
 * )
 * @Hateoas\Relation(
 *     "delete_item",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_delete_item",
 *         parameters={ "id": "expr(object.id)" }
 *     )
 * )
 */
class ItemView extends NodeView
{
    /**
     * @var string|null
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"offered_item", "favorite_item"})
     * @Serializer\Groups({"offered_item", "favorite_item"})
     */
    public $listId;

    /**
     * @var string|null
     *
     * @SWG\Property(example="-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 ....")
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $secret;

    /**
     * @var InviteItemView[]
     */
    public $invited;

    /**
     * @var ChildItemView|null
     */
    public $shared;

    /**
     * @var ChildItemView[]
     * @Groups({"child_item"})
     * @Serializer\Groups({"child_item"})
     */
    public $items;

    /**
     * @var UpdateView|null
     */
    public $update;

    /**
     * @var \DateTime
     * @Groups({"offered_item"})
     */
    public $lastUpdated;

    /**
     * @var string
     *
     * @SWG\Property(example="credentials")
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $type;

    /**
     * @var string[]
     */
    public $tags;

    /**
     * @Groups({"favorite_item"})
     * @Serializer\Groups({"favorite_item"})
     *
     * @var bool
     */
    public $favorite = false;

    /**
     * @var string|null
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $ownerId;

    /**
     * @var int
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $sort;

    /**
     * @Groups({"child_item", "offered_item"})
     * @Serializer\Groups({"child_item", "offered_item"})
     *
     * @var string|null
     */
    public $originalItemId;

    /**
     * @var string|null
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $previousListId;

    /**
     * @var Item|null
     *
     * @Serializer\Exclude
     * @SWG\Property(type="string")
     */
    private $item;

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }
}
