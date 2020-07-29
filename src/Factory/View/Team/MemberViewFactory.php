<?php

declare(strict_types=1);

namespace App\Factory\View\Team;

use App\Entity\UserTeam;
use App\Model\View\Team\MemberView;
use Symfony\Component\Security\Core\Security;

class MemberViewFactory
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function createSingle(UserTeam $userTeam): MemberView
    {
        if (null === $userTeam->getTeam() || null === $userTeam->getUser()) {
            throw new \BadMethodCallException('Incomplete UserTeam entity');
        }

        $currentUserTeam = $userTeam->getTeam()->getUserTeamByUser($this->security->getUser());
        $user = $userTeam->getUser();

        $view = new MemberView($currentUserTeam, $userTeam->getTeam());
        $view->setId($user->getId()->toString());
        $view->setName($user->getUsername());
        $view->setEmail($user->getEmail());
        $view->setAvatar($user->getAvatarLink());
        $view->setPublicKey($user->getPublicKey());
        $view->setRole($userTeam->getUserRole());
        $view->setTeamIds($user->getTeamsIds());

        return $view;
    }

    /**
     * @param UserTeam[] $users
     *
     * @return MemberView[]
     */
    public function createCollection(array $users): array
    {
        return array_map([$this, 'createSingle'], $users);
    }
}
