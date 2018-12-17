<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class ShareView
{
    /**
     * @var string
     *
     * @SWG\Property(example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     *
     * @Groups({"share_create", "share_read", "user_read"})
     */
    public $id;

    /**
     * @var \DateTime
     *
     * @Groups({"share_create", "share_read", "user_read"})
     */
    public $createdAt;

    /**
     * @var \DateTime
     *
     * @Groups({"share_read", "user_read"})
     */
    public $updatedAt;

    /**
     * @var string
     *
     * @SWG\Property(example="email@email")
     *
     * @Groups({"share_read", "user_read"})
     */
    public $email;

    /**
     * @var SharePostView[]
     *
     * @Groups({"share_read"})
     */
    public $sharedPosts = [];
}
