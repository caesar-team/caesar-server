<?php

declare(strict_types=1);

namespace App\JsonRpc\Method;

use App\Entity\User;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class UpdateUserKeysMethod implements JsonRpcMethodInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
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

        $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);
        $this->repository->save($user);

        return ['status' => $user->getFlowStatus()];
    }
}
