<?php

declare(strict_types=1);

namespace App\Model\View\Audit;

use Swagger\Annotations as SWG;

final class PostEventView extends AbstractEventView
{
    /**
     * @var string|null
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $post;

    /**
     * @var string|null
     *
     * @SWG\Property(example="1acc7aef-2fd6-3c16-5e4b-5c37486c7d34")
     */
    public $originalPost;
}
