<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Entity\Share;
use App\Model\View\Share\ShareView;

final class ShareViewFactory
{
    /**
     * @var SharePostViewFactory
     */
    private $sharePostViewFactory;

    public function __construct(SharePostViewFactory $sharePostViewFactory)
    {
        $this->sharePostViewFactory = $sharePostViewFactory;
    }

    public function create(Share $share): ShareView
    {
        $view = new ShareView();

        $view->id = $share->getId();
        $view->email = $share->getUser()->getEmail();
        $view->createdAt = $share->getCreatedAt();
        $view->updatedAt = $share->getUpdatedAt();
        foreach ($share->getSharedPosts() as $sharePost) {
            $view->sharedPosts[] = $this->sharePostViewFactory->create($sharePost);
        }

        return $view;
    }
}
