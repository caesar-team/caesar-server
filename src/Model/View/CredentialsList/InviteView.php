<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Swagger\Annotations as AppSwagger;
use Swagger\Annotations as SWG;

class InviteView
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
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $userId;

    /**
     * @var \DateTime
     */
    public $lastUpdated;

    /**
     * @var string
     *
     * @AppSwagger\EnumProperty(enumPath="App\DBAL\Types\Enum\AccessEnumType")
     */
    public $access;

    /**
     * @var string
     *
     * @SWG\Property(example="user@mail.com")
     */
    public $email;
}
