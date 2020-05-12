<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Entity\Item;
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
     */
    public $originalItemId;

    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"child_item"})
     */
    public $userId;

    /**
     * @var string|null
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"child_item"})
     */
    public $teamId;

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

    /**
     * @var string
     */
    public $link;
    /**
     * @var bool
     */
    public $isAccepted;
    /**
     * @var string
     */
    public $publicKey;

    public static function create(Item $item): self
    {
        $view = new self();
        $view->id = $item->getId()->toString();
        $view->userId = '';
        $view->lastUpdated = $item->getLastUpdated();

        return $view;
    }
}
