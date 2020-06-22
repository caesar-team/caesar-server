<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class ItemView extends NodeView
{
    /**
     * @var string|null
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     * @Groups({"offered_item", "favorite_item"})
     */
    public $listId;

    /**
     * @var string|null
     *
     * @SWG\Property(example="-----BEGIN PGP MESSAGE----- Version: OpenPGP.js v4.2.2 ....")
     * @Groups({"offered_item"})
     */
    public $secret;

    /**
     * @var InviteItemView[]
     */
    public $invited;

    /**
     * @var ChildItemView|null
     */
    public $shared;

    /**
     * @var ChildItemView[]
     * @Groups({"child_item"})
     */
    public $items;

    /**
     * @var UpdateView|null
     */
    public $update;

    /**
     * @var \DateTime
     * @Groups({"offered_item"})
     */
    public $lastUpdated;

    /**
     * @var string
     *
     * @SWG\Property(example="credentials")
     * @Groups({"offered_item"})
     */
    public $type;

    /**
     * @var string[]
     */
    public $tags;

    /**
     * @Groups({"favorite_item"})
     *
     * @var bool
     */
    public $favorite = false;

    /**
     * @var string|null
     * @Groups({"offered_item"})
     */
    public $ownerId;

    /**
     * @var int
     * @Groups({"offered_item"})
     */
    public $sort;

    /**
     * @Groups({"child_item", "offered_item"})
     *
     * @var string|null
     */
    public $originalItemId;

    /**
     * @var string|null
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $previousListId;
}
