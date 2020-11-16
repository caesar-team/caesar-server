<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use App\Entity\Fingerprint;
use App\Entity\User;

interface FingerprintRepositoryInterface
{
    public function save(Fingerprint $fingerprint): void;

    public function getFingerprint(User $user, string $fingerprint): ?Fingerprint;

    public function removeFingerprints(User $user): void;
}
