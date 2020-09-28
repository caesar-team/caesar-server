<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use App\Entity\Fingerprint;
use Symfony\Component\HttpFoundation\Request;

interface FingerprintFactoryInterface
{
    public function createFromRequest(Request $request): Fingerprint;
}
