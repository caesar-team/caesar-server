<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class ItemView extends NodeView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $listId;

    /**
     * @var string
     *
     * @SWG\Property(example="-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 ....")
     */
    public $secret;

    /**
     * @var string[]
     *
     * @SWG\Property(example={"4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46"})
     */
    public $shared;

    /**
     * @var \DateTime
     */
    public $lastUpdated;

    /**
     * @var string
     *
     * @SWG\Property(example="credentials")
     */
    public $type;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var string[]
     */
    public $tags;

    /**
     * @var bool
     */
    public $favorite = false;
}
