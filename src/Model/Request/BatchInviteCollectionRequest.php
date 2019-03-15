<?php

declare(strict_types=1);

namespace App\Model\Request;

use Doctrine\Common\Collections\ArrayCollection;

class BatchInviteCollectionRequest
{
    /**
     * @var InviteCollectionRequest[]
     */
    private $inviteCollectionList;

    public function __construct()
    {
        $this->inviteCollectionList = new ArrayCollection();
    }

    /**
     * @return InviteCollectionRequest[]|ArrayCollection
     */
    public function getInviteCollectionList(): ArrayCollection
    {
        return $this->inviteCollectionList;
    }

    /**
     * @param InviteCollectionRequest $inviteCollectionRequest
     */
    public function addInviteCollectionRequest(InviteCollectionRequest $inviteCollectionRequest): void
    {
        if (false === $this->inviteCollectionList->contains($inviteCollectionRequest)) {
            $this->inviteCollectionList->add($inviteCollectionRequest);
        }
    }
}