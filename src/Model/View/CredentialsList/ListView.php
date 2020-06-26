<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Entity\Directory;
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
 *         excludeIf="expr(not is_granted(constant('App\\Security\\ListVoter::DELETE'), object.getDirectory()))"
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
 *         excludeIf="expr(not is_granted(constant('App\\Security\\ListVoter::SORT'), object.getDirectory()))"
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
 *         excludeIf="expr(not is_granted(constant('App\\Security\\ListVoter::EDIT'), object.getDirectory()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "create_item",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_create_item"),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\ListVoter::CREATE_ITEM'), object.getDirectory()))"
 *     )
 * )
 */
class ListView extends NodeView
{
    /**
     * @var \App\Model\View\Item\ItemView[]
     */
    public $children;

    /**
     * @var string|null
     *
     * @SWG\Property(example="lists")
     */
    public $label;

    /**
     * @var string|null
     */
    public $teamId;

    /**
     * @var Directory|null
     *
     * @Serializer\Exclude
     * @SWG\Property(type="string")
     */
    private $directory;

    public function getDirectory(): ?Directory
    {
        return $this->directory;
    }

    public function setDirectory(?Directory $directory): void
    {
        $this->directory = $directory;
    }
}
