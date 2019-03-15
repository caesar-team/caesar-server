<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Entity\Item;
use Doctrine\Common\Collections\ArrayCollection;

class InviteCollectionRequest
{
    /**
     * @var Invite[]|ArrayCollection
     */
    protected $invites;

    /**
     * @var Item|null
     */
    protected $item;

    public function __construct(Item $item = null)
    {
        $this->item = $item;
        $this->invites = new ArrayCollection();
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    /**
     * @return Invite[]|ArrayCollection
     */
    public function getInvites(): ArrayCollection
    {
        return $this->invites;
    }

    public function addInvite(Invite $invite)
    {
        if (false === $this->invites->contains($invite)) {
            $this->invites->add($invite);
        }
    }

    public function removeInvite(Invite $invite)
    {
        $this->invites->removeElement($invite);
    }

    /**
     * @param Item $item
     */
    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }
}
