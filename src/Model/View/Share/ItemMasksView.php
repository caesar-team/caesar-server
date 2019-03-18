<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use Symfony\Component\Serializer\Annotation\Groups;

class ItemMasksView
{
    /**
     * @var ItemMaskView[]
     * @Groups({"create_child_item"})
     */
    public $masks = [];

    public function addItemMask(ItemMaskView $maskView): void
    {
        $this->masks[] = $maskView;
    }
}