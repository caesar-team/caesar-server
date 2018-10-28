<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class ListView extends NodeView
{
    /**
     * @var PostView[]
     */
    public $children;

    /**
     * @var string
     *
     * @SWG\Property(example="lists")
     */
    public $label;
}
