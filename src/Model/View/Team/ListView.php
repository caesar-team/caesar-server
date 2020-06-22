<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\Directory;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\NodeView;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
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
final class ListView extends NodeView
{
    /**
     * @var string|null
     *
     * @SWG\Property(type="string")
     */
    public $label;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="ItemView[]")
     */
    public $children;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string")
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
