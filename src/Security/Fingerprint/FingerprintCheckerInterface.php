<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use App\Entity\User;

interface FingerprintCheckerInterface
{
    public function hasValidFingerprint(User $user): bool;
}
