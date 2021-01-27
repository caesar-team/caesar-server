<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use App\Entity\Team;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class TeamDirectory extends AbstractDirectory
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Team", inversedBy="directories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private Team $team;

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }
}
