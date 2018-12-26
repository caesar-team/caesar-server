<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Share;
use Doctrine\Common\Collections\ArrayCollection;

class ShareCollectionRequest
{
    /**
     * @var Share[]|ArrayCollection
     */
    private $shares;

    public function __construct()
    {
        $this->shares = new ArrayCollection();
    }

    /**
     * @return Share[]|ArrayCollection
     */
    public function getShares(): ArrayCollection
    {
        return $this->shares;
    }

    /**
     * @param Share[]|ArrayCollection $shares
     */
    public function setShares($shares): void
    {
        $this->shares = $shares;
    }
}
