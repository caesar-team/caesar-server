<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\User;

class UserQuery extends AbstractQuery
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var User
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
