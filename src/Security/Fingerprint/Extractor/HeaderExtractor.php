<?php

declare(strict_types=1);

namespace App\Security\Fingerprint\Extractor;

use App\Security\Fingerprint\FingerprintExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class HeaderExtractor implements FingerprintExtractorInterface
{
    public const NAME_PARAM = 'x-fingerprint';

    public function extract(Request $request): ?string
    {
        return $request->headers->get(self::NAME_PARAM);
    }
}
