<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class CreatedItemView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $id;

    /**
     * @var \DateTime
     */
    public $lastUpdated;
}
