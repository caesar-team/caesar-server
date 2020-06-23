<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Model\View\CredentialsList\ItemView;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Groups;

class TeamItemsView
{
    /**
     * @var string
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $id;

    /**
     * @var ItemView[]
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $items;
}
