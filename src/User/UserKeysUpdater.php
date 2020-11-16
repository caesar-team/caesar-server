<?php

declare(strict_types=1);

namespace App\User;

use App\Entity\User;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Request\User\SaveKeysRequest;

class UserKeysUpdater
{
    private UserRepository $userRepository;

    private InvitationRepository $invitationRepository;

    public function __construct(UserRepository $userRepository, InvitationRepository $invitationRepository)
    {
        $this->userRepository = $userRepository;
        $this->invitationRepository = $invitationRepository;
    }

    public function updateKeysFromRequest(SaveKeysRequest $request): void
    {
        $user = $request->getUser();
        $user->setEncryptedPrivateKey($request->getEncryptedPrivateKey());
        $user->setPublicKey($request->getPublicKey());
        $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);

        $this->userRepository->save($user);
    }

    public function saveKeysFromRequest(SaveKeysRequest $request): void
    {
        $user = $request->getUser();
        if (!$user->isFullUser()
            || (
                $request->getEncryptedPrivateKey() !== $user->getEncryptedPrivateKey()
                && User::FLOW_STATUS_CHANGE_PASSWORD !== $user->getFlowStatus()
            )
        ) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }

        if ($user->isFullUser()) {
            $this->invitationRepository->deleteByHash($user->getHashEmail());
        }

        $user->setEncryptedPrivateKey($request->getEncryptedPrivateKey());
        $user->setPublicKey($request->getPublicKey());

        $this->userRepository->save($user);
    }
}
