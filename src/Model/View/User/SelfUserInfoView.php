<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SelfUserInfoView
{
    /**
     * @var int
     *
     * @SWG\Property(example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="ipopov@4xxi.com")
     */
    public $email;

    /**
     * @var string
     *
     * @SWG\Property(example="static/images/user/b3d4d910-bf9d-4718-b93c-553f1e6711bb.jpeg")
     */
    public $avatar;
}
