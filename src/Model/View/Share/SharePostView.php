<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class SharePostView
{
    /**
     * @var string
     *
     * @SWG\Property(example="a68833f.....4db3ac47641b9e")
     *
     * @Groups({"share_read"})
     */
    public $secret;
}
