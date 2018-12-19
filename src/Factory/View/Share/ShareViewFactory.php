<?php

declare(strict_types=1);

namespace App\Factory\View\Share;

use App\Entity\Share;
use App\Model\View\Share\ShareView;

final class ShareViewFactory
{
    /**
     * @var ShareItemViewFactory
     */
    private $shareItemViewFactory;

    public function __construct(ShareItemViewFactory $shareItemViewFactory)
    {
        $this->shareItemViewFactory = $shareItemViewFactory;
    }

    public function create(Share $share): ShareView
    {
        $view = new ShareView();

        $view->id = $share->getId();
        $view->email = $share->getUser()->getEmail();
        $view->createdAt = $share->getCreatedAt();
        $view->updatedAt = $share->getUpdatedAt();
        foreach ($share->getSharedItems() as $sharedItem) {
            $view->sharedItems[] = $this->shareItemViewFactory->create($sharedItem);
        }

        return $view;
    }
}
