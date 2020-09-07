<?php

declare(strict_types=1);

namespace App\Security\Fingerprint\Extractor;

use App\Security\Fingerprint\FingerprintExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class QueryExtractor implements FingerprintExtractorInterface
{
    public const NAME_PARAM = 'fingerprint';

    public function extract(Request $request): ?string
    {
        return $request->get(self::NAME_PARAM);
    }
}
