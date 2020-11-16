<?php

declare(strict_types=1);

namespace App\Limiter\Inspector;

use App\Repository\UserRepository;

final class UserCountInspector extends AbstractInspector implements InspectorInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUsed(int $addedSize = 0): int
    {
        return $this->repository->getCountActiveUsers() + $addedSize;
    }

    public function getErrorMessage(): string
    {
        return 'limiter.exception.user_count';
    }
}
