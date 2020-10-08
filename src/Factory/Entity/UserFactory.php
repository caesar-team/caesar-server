<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Srp;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Request\User\CreateInvitedUserRequest;

class UserFactory
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createFromInvitedRequest(CreateInvitedUserRequest $request): User
    {
        $user = $this->repository->findWithoutPublicKey([
            'email' => $request->getEmail(),
        ]);

        $user = $user ?? new User(new Srp());
        $user->setEmail($request->getEmail());
        $user->setUsername($request->getEmail());
        $user->setEnabled(true);
        $user->setPlainPassword($request->getPlainPassword());
        $user->setEncryptedPrivateKey($request->getEncryptedPrivateKey());
        $user->setPublicKey($request->getPublicKey());
        $user->setRoles($request->getRoles());
        if ($user->hasRole(User::ROLE_READ_ONLY_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);
        }
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }

        $this->setSrp($user, $request);

        return $user;
    }

    private function setSrp(User $user, CreateInvitedUserRequest $request): void
    {
        $srp = $user->getSrp() ?? new Srp();
        $srp->setSeed($request->getSeed());
        $srp->setVerifier($request->getVerifier());

        $user->setSrp($srp);
    }
}
