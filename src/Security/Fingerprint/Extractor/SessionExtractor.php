<?php

declare(strict_types=1);

namespace App\Security\Fingerprint\Extractor;

use App\Security\Fingerprint\FingerprintExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class SessionExtractor implements FingerprintExtractorInterface
{
    public const NAME_PARAM = '_fingerprint';

    public function extract(Request $request): ?string
    {
        if (null === $request->getSession()) {
            return null;
        }

        return $request->getSession()->get(self::NAME_PARAM);
    }
}
