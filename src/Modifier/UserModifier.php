<?php

declare(strict_types=1);

namespace App\Modifier;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\User\TwoFactoryAuthEnableRequest;

class UserModifier
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function modifyByRequest(TwoFactoryAuthEnableRequest $request): User
    {
        $user = $request->getUser();
        $user->setGoogleAuthenticatorSecret($request->getSecret());

        $this->repository->save($user);

        return $user;
    }
}
