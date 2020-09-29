<?php

declare(strict_types=1);

namespace App\Team\UserTeamCollector;

use App\Entity\User;
use App\Team\UserTeamCollectorInterface;
use Symfony\Component\Security\Core\Security;

class CreatorUserTeamCollector implements UserTeamCollectorInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function collect(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return [];
        }

        return [$user];
    }
}
