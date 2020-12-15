<?php

declare(strict_types=1);

namespace App\Security\Domain;

use App\Security\Domain\Repository\AllowedDomainRepositoryInterface;
use App\Security\Domain\Util\EmailParser;

final class DomainChecker implements DomainCheckerInterface
{
    private AllowedDomainRepositoryInterface $repository;

    public function __construct(AllowedDomainRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function check(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $allowedDomains = $this->repository->getAllowedDomains();
        if (empty($allowedDomains)) {
            return true;
        }

        $emailDomain = EmailParser::getEmailDomain($email);
        if (null === $emailDomain) {
            return false;
        }

        return in_array($emailDomain, $allowedDomains, true);
    }
}
