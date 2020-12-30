<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Embedded\ItemMeta;
use App\Factory\Entity\Directory\DirectoryItemFactory;
use App\Model\DTO\Member;
use App\Request\Team\CreateMemberRequest;

class MemberFactory
{
    private UserTeamFactory $userTeamFactory;

    private ItemFactory $itemFactory;

    private DirectoryItemFactory $directoryItemFactory;

    public function __construct(UserTeamFactory $userTeamFactory, ItemFactory $itemFactory, DirectoryItemFactory $directoryItemFactory)
    {
        $this->userTeamFactory = $userTeamFactory;
        $this->itemFactory = $itemFactory;
        $this->directoryItemFactory = $directoryItemFactory;
    }

    public function createFromRequest(CreateMemberRequest $request): Member
    {
        $team = $request->getTeam();
        $user = $request->getUser();

        $userTeam = $this->userTeamFactory->create();
        $userTeam->setUserRole($request->getTeamRole());
        $userTeam->setUser($user);
        $userTeam->setTeam($team);

        $keypair = $this->itemFactory->create();
        $keypair->setOwner($user);
        $keypair->setTeam($team);
        $keypair->setType(NodeEnumType::TYPE_KEYPAIR);
        $keypair->setSecret($request->getSecret());
        $meta = new ItemMeta();
        $meta->setTitle(NodeEnumType::TYPE_KEYPAIR);
        $keypair->setMeta($meta);

        $this->directoryItemFactory->create($keypair, $team->getDefaultDirectory());

        return new Member($userTeam, $keypair);
    }
}
