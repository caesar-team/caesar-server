<?php

declare(strict_types=1);

namespace App\Factory\View\Srp;

use App\Entity\Srp;
use App\Model\View\Srp\PreparedSrpView;

class SrpPrepareViewFactory
{
    public function createSingle(Srp $srp): PreparedSrpView
    {
        $view = new PreparedSrpView();

        $view->setPublicEphemeralValue($srp->getPublicServerEphemeralValue());
        $view->setSeed($srp->getSeed());

        return $view;
    }
}
