<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class ItemsAllQuery
{
    private User $user;

    private ?\DateTime $lastUpdated = null;

    public function __construct(User $user, Request $request)
    {
        $this->user = $user;

        $lastUpdated = \DateTime::createFromFormat('U', (string) $request->query->getInt('lastUpdated', 0));
        if ($lastUpdated instanceof \DateTime) {
            $this->lastUpdated = $lastUpdated;
        }
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->lastUpdated;
    }
}
