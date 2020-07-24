<?php

declare(strict_types=1);

namespace App\JsonRpc\Method;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\InvitationManager;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class ChangeUserKeysMethod implements JsonRpcMethodInterface
{
    private UserRepository $repository;

    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function apply(array $paramList = null)
    {
        $userId = $paramList['user'] ?? null;
        if (!Uuid::isValid($userId)) {
            return ['error' => 'User not found'];
        }

        $user = $this->repository->find($userId);
        if (null === $user) {
            return ['error' => 'User not found'];
        }

        if (!$user->isFullUser()
            || User::FLOW_STATUS_CHANGE_PASSWORD !== $user->getFlowStatus()
        ) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }

        if ($user->isFullUser()) {
            InvitationManager::removeInvitation($user, $this->entityManager);
        }

        $this->entityManager->flush();

        return ['status' => $user->getFlowStatus()];
    }
}
