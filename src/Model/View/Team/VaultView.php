<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Model\View\Item\ItemView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class VaultView
{
    /**
     * @var TeamView
     *
     * @SWG\Property(@Model(type=TeamView::class))
     */
    private ?TeamView $team;

    /**
     * @var ItemView
     *
     * @SWG\Property(@Model(type=ItemView::class))
     */
    private ?ItemView $keypair;

    public function __construct()
    {
        $this->team = null;
        $this->keypair = null;
    }

    public function getTeam(): ?TeamView
    {
        return $this->team;
    }

    public function setTeam(TeamView $team): void
    {
        $this->team = $team;
    }

    public function getKeypair(): ?ItemView
    {
        return $this->keypair;
    }

    public function setKeypair(ItemView $keypair): void
    {
        $this->keypair = $keypair;
    }
}
