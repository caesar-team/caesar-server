<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Swagger\Annotations as AppSwagger;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class ChildItemView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"child_item"})
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"child_item"})
     */
    public $userId;

    /**
     * @var \DateTime
     * @Groups({"child_item"})
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
