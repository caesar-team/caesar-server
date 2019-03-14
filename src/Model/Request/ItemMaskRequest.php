<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\ItemMask;

class ItemMaskRequest
{
    /**
     * @var ItemMask
     */
    private $itemMask;

    /**
     * @return ItemMask
     */
    public function getItemMask(): ?ItemMask
    {
        return $this->itemMask;
    }

    /**
     * @param ItemMask $itemMask
     */
    public function setItemMask(ItemMask $itemMask): void
    {
        $this->itemMask = $itemMask;
    }
}