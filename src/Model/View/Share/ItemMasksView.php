<?php

declare(strict_types=1);

namespace App\Model\View\Share;


class ItemMasksView
{
    /**
     * @var ItemMaskView[]
     */
    public $masks;

    public function addItemMask(ItemMaskView $maskView): void
    {
        $this->masks[] = $maskView;
    }
}