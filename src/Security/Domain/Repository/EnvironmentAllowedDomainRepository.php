<?php

declare(strict_types=1);

namespace App\Security\Domain\Repository;

final class EnvironmentAllowedDomainRepository implements AllowedDomainRepositoryInterface
{
    private string $allowedDomains;

    public function __construct(string $allowedDomains)
    {
        $this->allowedDomains = $allowedDomains;
    }

    /**
     * @return string[]
     */
    public function getAllowedDomains(): array
    {
        return explode(',', $this->allowedDomains);
    }
}
