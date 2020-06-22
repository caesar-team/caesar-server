<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Hateoas\Relation(
 *     "team_create",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_team_create"
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         groups={"public"},
 *         excludeIf="expr(not is_granted('ROLE_ADMIN'))"
 *     )
 * )
 *
 * @Hateoas\Relation(
 *     "create_list",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_create_list"
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         groups={"public"},
 *         excludeIf="expr(not is_granted('ROLE_USER'))"
 *     )
 * )
 */
class SelfUserInfoView
{
    /**
     * @var string
     *
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(type="string", example="ipopov@4xxi.com")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $email;

    /**
     * @var string
     *
     * @SWG\Property(type="string", example="ipopov")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(type="string", example="static/images/user/b3d4d910-bf9d-4718-b93c-553f1e6711bb.jpeg")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $avatar;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example="['ROLE_USER']")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $roles = [];

    /**
     * @var string[]
     */
    public $teamIds = [];
}
