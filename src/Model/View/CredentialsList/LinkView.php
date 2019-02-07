<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class LinkView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="key")
     */
    public $publicKey;

    /**
     * @var string
     *
     * @SWG\Property(example="encrypted data to right link association")
     */
    public $data;
}
