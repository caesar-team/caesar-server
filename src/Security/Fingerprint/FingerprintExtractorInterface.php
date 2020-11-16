<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use Symfony\Component\HttpFoundation\Request;

interface FingerprintExtractorInterface
{
    public function extract(Request $request): ?string;
}
