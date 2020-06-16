<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\Common\Collections\Collection;

class UserQuery extends AbstractQuery
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var UserTeam[]|Collection
     */
    public $userTeams;

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userTeams = $user->getUserTeams();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return UserTeam[]
     */
    public function getUserTeams(): array
    {
        return $this->userTeams->toArray();
    }
}
