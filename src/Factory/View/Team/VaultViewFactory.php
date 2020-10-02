<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Factory\View\Item\ItemViewFactory;
use App\Model\DTO\Vault;
use App\Model\View\Team\VaultView;

class VaultViewFactory
{
    private TeamViewFactory $teamViewFactory;

    private ItemViewFactory $itemViewFactory;

    public function __construct(TeamViewFactory $teamViewFactory, ItemViewFactory $itemViewFactory)
    {
        $this->teamViewFactory = $teamViewFactory;
        $this->itemViewFactory = $itemViewFactory;
    }

    public function createSingle(Vault $vault): VaultView
    {
        $view = new VaultView();
        $view->setTeam($this->teamViewFactory->createSingle($vault->getTeam()));
        $view->setKeypair($this->itemViewFactory->createSingle($vault->getKeypair()));

        return $view;
    }
}
