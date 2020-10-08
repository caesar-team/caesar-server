<?php

declare(strict_types=1);

namespace App\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateBatchInvitedUserRequest
{
    /**
     * @var CreateInvitedUserRequest[]
     *
     * @Assert\Valid()
     */
    private array $users;

    public function __construct()
    {
        $this->users = [];
    }

    /**
     * @return CreateInvitedUserRequest[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param CreateInvitedUserRequest[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }
}
