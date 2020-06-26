<?php

declare(strict_types=1);

namespace App\Factory\View\User;

use App\Entity\User;
use App\Model\View\User\PublicUserKeyView;

final class PublicUserKeyViewFactory
{
    public function createSingle(User $user): PublicUserKeyView
    {
        $view = new PublicUserKeyView();
        $view->setUserId($user->getId()->toString());
        $view->setPublicKey($user->getPublicKey());
        $view->setEmail($user->getEmail());

        return $view;
    }

    /**
     * @param User[] $users
     *
     * @return PublicUserKeyView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
