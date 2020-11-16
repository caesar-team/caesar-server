<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

final class FingerprintChecker implements FingerprintCheckerInterface
{
    private RequestStack $requestStack;

    private FingerprintExtractorInterface $extractor;

    private FingerprintRepositoryInterface $repository;

    public function __construct(
        RequestStack $requestStack,
        FingerprintExtractorInterface $extractor,
        FingerprintRepositoryInterface $repository
    ) {
        $this->requestStack = $requestStack;
        $this->extractor = $extractor;
        $this->repository = $repository;
    }

    public function hasValidFingerprint(User $user): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return false;
        }

        $requestFingerprint = $this->extractor->extract($request);
        if (null === $requestFingerprint) {
            return false;
        }
        $fingerprint = $this->repository->getFingerprint($user, $requestFingerprint);
        if (null === $fingerprint) {
            return false;
        }

        return $fingerprint->isValidExpired();
    }
}
