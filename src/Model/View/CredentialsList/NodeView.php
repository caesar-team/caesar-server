<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;
use App\Swagger\Annotations as AppSwagger;
use Symfony\Component\Serializer\Annotation\Groups;

class NodeView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"offered_item"})
     */
    public $id;

    /**
     * @var string
     *
     * @AppSwagger\EnumProperty(enumPath="App\DBAL\Types\Enum\NodeEnumType")
     * @Groups({"offered_item"})
     */
    public $type;

    /**
     * @var int
     */
    public $sort;
}
