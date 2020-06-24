<?php

declare(strict_types=1);

namespace App\Model\View\Item;

final class OfferedItemsView
{
    /**
     * @var OfferedItemView[]
     */
    private array $personal;

    /**
     * @var OfferedTeamItemsView[]
     */
    private array $teams;

    public function __construct(array $personal = [], array $teams = [])
    {
        $this->personal = $personal;
        $this->teams = $teams;
    }

    /**
     * @return OfferedItemView[]
     */
    public function getPersonal(): array
    {
        return $this->personal;
    }

    /**
     * @return OfferedTeamItemsView[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }
}
