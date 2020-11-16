<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\UserKeysView;

class UserKeysViewFactory
{
    public function createSingle(User $user): UserKeysView
    {
        $view = new UserKeysView();
        $view->setEncryptedPrivateKey($user->getEncryptedPrivateKey());
        $view->setPublicKey($user->getPublicKey());

        return $view;
    }
}
