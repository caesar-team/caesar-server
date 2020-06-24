<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Swagger\Annotations as AppSwagger;
use Swagger\Annotations as SWG;

class NodeView
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
     * @AppSwagger\EnumProperty(enumPath="App\DBAL\Types\Enum\NodeEnumType")
     */
    public $type;

    /**
     * @var int
     */
    public $sort;

    public function getId(): string
    {
        return $this->id;
    }
}
