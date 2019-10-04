<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class SelfUserInfoView
{
    /**
     * @var string
     *
     * @SWG\Property(example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     * @Groups({"public"})
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="ipopov@4xxi.com")
     * @Groups({"public"})
     */
    public $email;

    /**
     * @var string
     *
     * @SWG\Property(example="ipopov")
     * @Groups({"public"})
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(example="static/images/user/b3d4d910-bf9d-4718-b93c-553f1e6711bb.jpeg")
     * @Groups({"public"})
     */
    public $avatar;

    /**
     * @var string[]
     * @SWG\Property(example="['ROLE_USER']")
     * @Groups({"public"})
     */
    public $roles = [];

    /**
     * @var string[]
     */
    public $teamIds = [];
}
