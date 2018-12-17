<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Entity\SharePost;
use App\Model\View\Share\SharePostView;

final class SharePostViewFactory
{
    public function create(SharePost $sharePost): SharePostView
    {
        $view = new SharePostView();
        $view->secret = $sharePost->getSecret();

        return $view;
    }
}
