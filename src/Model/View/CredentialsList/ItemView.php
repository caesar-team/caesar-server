<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use App\Model\View\User\UserView;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @var ChildItemView[]
     */
    public $invited;

    /**
     * @var ChildItemView
     */
    public $shared;

    /**
     * @var ChildItemView[]
     * @Groups({"child_item"})
     */
    public $items;

    /**
     * @var UpdateView
     */
    public $update;

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
     * @var string[]
     */
    public $tags;

    /**
     * @var bool
     */
    public $favorite = false;
    /**
     * @var UserView
     */
    public $owner;

    /**
     * @var int
     */
    public $sort;
    /**
     * @Groups({"child_item"})
     * @var null|string
     */
    public $originalItemId;
    /**
     * @var null|string
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $previousListId;
}
