<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\User;
use App\Model\View\User\UserKeysView;

class UserKeysViewFactory
{
    public function create(User $user): ?UserKeysView
    {
        if (null === $user->getEncryptedPrivateKey() && null === $user->getPublicKey()) {
            return null;
        }

        $view = new UserKeysView();

        $view->userId = $user->getId()->toString();
        $view->encryptedPrivateKey = $user->getEncryptedPrivateKey();
        $view->publicKey = $user->getPublicKey();
        $view->email = $user->getEmail();

        return $view;
    }
}
