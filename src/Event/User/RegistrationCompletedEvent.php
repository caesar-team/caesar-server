<?php

declare(strict_types=1);

namespace App\Event\User;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class RegistrationCompletedEvent extends Event
{
    public const FROM_APP = 'app';
    public const FROM_GOOGLE = 'google';

    private User $user;

    private string $from;

    public function __construct(User $user, string $from = self::FROM_APP)
    {
        $this->user = $user;
        $this->from = $from;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFrom(): string
    {
        return $this->from;
    }
}
