<?php

declare(strict_types=1);

namespace App\Services;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\UserTeam;
use App\Repository\UserTeamRepository;
use App\Utils\ChildItemAwareInterface;

class PermissionManager
{
    private UserTeamRepository $userTeamRepository;

    public function __construct(UserTeamRepository $userTeamRepository)
    {
        $this->userTeamRepository = $userTeamRepository;
    }

    public function getItemAccessLevel(ChildItemAwareInterface $item): string
    {
        $userTeam = $this
            ->userTeamRepository
            ->findOneByUserAndTeam($item->getSignedOwner(), $item->getTeam());

        if ($userTeam && UserTeam::USER_ROLE_ADMIN === $userTeam->getUserRole()) {
            return AccessEnumType::TYPE_WRITE;
        }

        return AccessEnumType::TYPE_READ;
    }
}