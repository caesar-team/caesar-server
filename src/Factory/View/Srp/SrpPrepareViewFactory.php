<?php

declare(strict_types=1);

namespace App\Factory\View\Srp;

use App\Entity\Srp;
use App\Model\View\Srp\PreparedSrpView;

class SrpPrepareViewFactory
{
    /**
     * @param Srp $srp
     *
     * @return PreparedSrpView
     */
    public function create(Srp $srp): PreparedSrpView
    {
        $view = new PreparedSrpView();

        $view->publicEphemeralValue = $srp->getPublicServerEphemeralValue();
        $view->seed = $srp->getSeed();

        return $view;
    }
}
