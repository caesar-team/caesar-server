<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Post;
use App\Model\View\CredentialsList\CreatedPostView;

class CreatedPostViewFactory
{
    public function create(Post $post): CreatedPostView
    {
        $view = new CreatedPostView();

        $view->id = $post->getId();
        $view->lastUpdated = $post->getLastUpdated();

        return $view;
    }
}
