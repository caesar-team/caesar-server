<?php

declare(strict_types=1);

namespace App\Model\Request;

use Doctrine\Common\Collections\ArrayCollection;

class ItemMaskCollctionRequest
{
    /**
     * @var ItemMaskRequest[]|ArrayCollection
     */
    private $masks;

    public function __construct()
    {
        $this->masks = new ArrayCollection();
    }

    /**
     * @return ItemMaskRequest[]|ArrayCollection
     */
    public function getMasks()
    {
        return $this->masks;
    }

    /**
     * @param ItemMaskRequest[]|ArrayCollection $masks
     */
    public function setMasks($masks): void
    {
        $this->masks = $masks;
    }

}