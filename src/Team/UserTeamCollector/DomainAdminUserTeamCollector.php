<?php

declare(strict_types=1);

namespace App\Team\UserTeamCollector;

use App\Repository\UserRepository;
use App\Team\UserTeamCollectorInterface;

class DomainAdminUserTeamCollector implements UserTeamCollectorInterface
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function collect(): array
    {
        return $this->repository->getDomainAdmins();
    }
}
