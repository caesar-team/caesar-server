<?php

declare(strict_types=1);

namespace App\Model\Request\Team;

use App\Entity\Team;
use App\Model\Request\BatchItemCollectionRequest;

class BatchTeamsItemsCollectionRequest
{
    /**
     * @var Team
     */
    private $team;

    /**
     * @var BatchItemCollectionRequest[]
     */
    private $shares = [];


    /**
     * @return array|BatchItemCollectionRequest[]
     */
    public function getShares(): array
    {
        return $this->shares;
    }

    public function setShares(array $shares): void
    {
        $this->shares = $shares;
    }

    public function addShare(BatchItemCollectionRequest $request): void
    {
        $this->shares[] = $request;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }
}