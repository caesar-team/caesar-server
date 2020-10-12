<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Model\DTO\Member;
use App\Request\Team\CreateMemberRequest;

class MemberFactory
{
    private UserTeamFactory $userTeamFactory;

    private ItemFactory $itemFactory;

    public function __construct(UserTeamFactory $userTeamFactory, ItemFactory $itemFactory)
    {
        $this->userTeamFactory = $userTeamFactory;
        $this->itemFactory = $itemFactory;
    }

    public function createFromRequest(CreateMemberRequest $request): Member
    {
        $team = $request->getTeam();
        $user = $request->getUser();

        $userTeam = $this->userTeamFactory->create();
        $userTeam->setUserRole($request->getUserRole());
        $userTeam->setUser($user);
        $userTeam->setTeam($team);

        $keypair = $this->itemFactory->create();
        $keypair->setOwner($user);
        $keypair->setTeam($team);
        $keypair->setType(NodeEnumType::TYPE_KEYPAIR);
        $keypair->setParentList($team->getDefaultDirectory());
        $keypair->setSecret($request->getSecret());

        return new Member($userTeam, $keypair);
    }
}
