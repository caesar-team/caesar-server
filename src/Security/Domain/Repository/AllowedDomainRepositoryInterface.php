<?php

declare(strict_types=1);

namespace App\Security\Domain\Repository;

interface AllowedDomainRepositoryInterface
{
    /**
     * @return string[]
     */
    public function getAllowedDomains(): array;
}
