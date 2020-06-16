<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class ListView extends NodeView
{
    /**
     * @var ItemView[]
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
}
