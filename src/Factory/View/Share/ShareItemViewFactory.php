<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Entity\ShareItem;
use App\Model\View\Share\ShareItemView;

final class ShareItemViewFactory
{
    public function create(ShareItem $shareItem): ShareItemView
    {
        $view = new ShareItemView();
        $view->secret = $shareItem->getSecret();

        return $view;
    }
}
