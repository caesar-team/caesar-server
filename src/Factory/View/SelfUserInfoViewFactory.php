<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Avatar;
use App\Entity\User;
use App\Factory\View\Share\ShareViewFactory;
use App\Model\View\User\SelfUserInfoView;

class SelfUserInfoViewFactory
{
    /**
     * @var ShareViewFactory
     */
    private $shareViewFactory;

    public function __construct(ShareViewFactory $shareViewFactory)
    {
        $this->shareViewFactory = $shareViewFactory;
    }

    /**
     * @param User $user
     *
     * @return SelfUserInfoView
     */
    public function create(User $user): SelfUserInfoView
    {
        $view = new SelfUserInfoView();

        $view->id = $user->getId();
        $view->email = $user->getEmail();
        $view->name = $user->getUsername();
        $view->avatar = $this->getImage($user->getAvatar());
        $view->roles = $user->getRoles();
        foreach ($user->getShares() as $share) {
            $view->shares[] = $this->shareViewFactory->create($share);
        }

        return $view;
    }

    private function getImage(?Avatar $avatar)
    {
        if (null === $avatar) {
            return null;
        }

        return $avatar->getLink();
    }
}
