<?php

declare(strict_types=1);

namespace App\Security\Request;

use Symfony\Component\HttpFoundation\Request;

interface RequestVerifierInterface
{
    public function verifyRequest(Request $request, string $publicKey): bool;
}
