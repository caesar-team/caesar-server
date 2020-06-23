<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\Model\View\CredentialsList\ItemView;
use App\Model\View\Team\TeamItemsView;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Groups;

final class OfferedItemsView
{
    /**
     * @var ItemView[]
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $personal;

    /**
     * @var TeamItemsView[]
     * @Groups({"offered_item"})
     * @Serializer\Groups({"offered_item"})
     */
    public $teams;

    public function __construct(array $personal = [], array $teams = [])
    {
        $this->personal = $personal;
        $this->teams = $teams;
    }
}
