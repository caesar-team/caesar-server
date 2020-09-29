<?php

declare(strict_types=1);

namespace App\Team\UserTeamCollector;

use App\Team\UserTeamCollectorInterface;

class UserTeamCollectorComposite implements UserTeamCollectorInterface
{
    /**
     * @var UserTeamCollectorInterface[]
     */
    private array $collectors;

    public function __construct(UserTeamCollectorInterface ...$collectors)
    {
        $this->collectors = $collectors;
    }

    public function collect(): array
    {
        $users = [];
        foreach ($this->collectors as $collector) {
            foreach ($collector->collect() as $user) {
                $users[$user->getId()->toString()] = $user;
            }
        }

        return $users;
    }
}
