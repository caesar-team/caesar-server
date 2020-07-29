<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Entity\Item;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
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
 *         parameters={ "id": "expr(object.getId())" }
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
 *         parameters={ "id": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamItemVoter::MOVE'), object.getItem()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_batch_share_item",
 *     attributes={"method": "POST"},
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
class CreatedItemView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="2020-06-24T08:03:12+00:00")
     */
    private \DateTime $lastUpdated;

    /**
     * @Serializer\Exclude()
     */
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTime $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
