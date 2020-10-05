<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\UserTeam;
use App\Model\DTO\Vault;
use App\Model\Request\Team\CreateVaultRequest;

class VaultFactory
{
    private TeamFactory $teamFactory;

    private ItemFactory $itemFactory;

    public function __construct(TeamFactory $teamFactory, ItemFactory $itemFactory)
    {
        $this->teamFactory = $teamFactory;
        $this->itemFactory = $itemFactory;
    }

    public function createFromRequest(CreateVaultRequest $request): Vault
    {
        $user = $request->getUser();
        $teamRequest = $request->getTeam();
        if (null === $teamRequest) {
            throw new \BadMethodCallException('Request should have team ');
        }
        $keypairRequest = $request->getKeypair();
        if (null === $keypairRequest) {
            throw new \BadMethodCallException('Request should have keypair');
        }

        $team = $this->teamFactory->create();
        $team->setTitle((string) $teamRequest->getTitle());
        $team->setIcon($teamRequest->getIcon());
        $team->addUserTeam(
            new UserTeam($user, $team, UserTeam::USER_ROLE_ADMIN)
        );

        $item = $this->itemFactory->create();
        $item->setOwner($user);
        $item->setTeam($team);
        $item->setType(NodeEnumType::TYPE_KEYPAIR);
        $item->setParentList($team->getDefaultDirectory());
        $item->setSecret($keypairRequest->getSecret());

        return new Vault($team, $item);
    }
}
