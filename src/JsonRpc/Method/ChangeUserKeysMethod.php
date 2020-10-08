<?php

declare(strict_types=1);

namespace App\JsonRpc\Method;

use App\Entity\User;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class ChangeUserKeysMethod implements JsonRpcMethodInterface
{
    private UserRepository $userRepository;

    private InvitationRepository $invitationRepository;

    public function __construct(
        UserRepository $userRepository,
        InvitationRepository $invitationRepository
    ) {
        $this->userRepository = $userRepository;
        $this->invitationRepository = $invitationRepository;
    }

    public function apply(array $paramList = null)
    {
        $userId = $paramList['user'] ?? null;
        if (!Uuid::isValid($userId)) {
            return ['error' => 'User not found'];
        }

        $user = $this->userRepository->find($userId);
        if (null === $user) {
            return ['error' => 'User not found'];
        }

        if (!$user->isFullUser()
            || User::FLOW_STATUS_CHANGE_PASSWORD !== $user->getFlowStatus()
        ) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }

        if ($user->isFullUser()) {
            $this->invitationRepository->deleteByHash($user->getHashEmail());
        }

        $this->userRepository->save($user);

        return ['status' => $user->getFlowStatus()];
    }
}
