<?php

declare(strict_types=1);

namespace App\Security\Domain;

interface DomainCheckerInterface
{
    public function check(string $email): bool;
}
