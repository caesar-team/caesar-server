<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\Common\Collections\ArrayCollection;

class UserQuery extends AbstractQuery
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var UserTeam[]|ArrayCollection
     */
    public $userGroups;

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userGroups = $user->getUserGroups();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return UserTeam[]
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }
}
