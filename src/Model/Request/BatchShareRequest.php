<?php

declare(strict_types=1);

namespace App\Model\Request;

use App\Model\Request\Team\BatchTeamsItemsCollectionRequest;

final class BatchShareRequest
{
    /**
     * @var BatchItemCollectionRequest[]
     */
    private $personals = [];

    /**
     * @var BatchTeamsItemsCollectionRequest[]
     */
    private $teams = [];

    /**
     * @return array|BatchItemCollectionRequest[]
     */
    public function getPersonals(): array
    {
        return $this->personals;
    }

    public function setPersonals(array $personals): void
    {
        $this->personals = $personals;
    }

    public function addPersonal(BatchItemCollectionRequest $request): void
    {
        $this->personals[] = $request;
    }

    /**
     * @return array|BatchTeamsItemsCollectionRequest[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    public function setTeams(array $teams): void
    {
        $this->teams = $teams;
    }

    public function addTeam(BatchTeamsItemsCollectionRequest $request): void
    {
        $this->teams[] = $request;
    }
}