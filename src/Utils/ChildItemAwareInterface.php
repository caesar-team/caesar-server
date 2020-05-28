<?php

namespace App\Utils;

use App\Entity\Team;
use App\Entity\User;

interface ChildItemAwareInterface
{
    public function getCause(): ?string;

    public function getTeam(): ?Team;

    public function getSignedOwner(): User;
}
