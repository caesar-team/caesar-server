<?php

declare(strict_types=1);

namespace App\Security\Fingerprint\Extractor;

use App\Security\Fingerprint\FingerprintExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final class CompositeExtractor implements FingerprintExtractorInterface
{
    /**
     * @var FingerprintExtractorInterface[]
     */
    private array $extractors;

    public function __construct(FingerprintExtractorInterface ...$extractors)
    {
        $this->extractors = $extractors;
    }

    public function extract(Request $request): ?string
    {
        foreach ($this->extractors as $extractor) {
            $fingerprint = $extractor->extract($request);
            if (null !== $fingerprint && !empty($fingerprint)) {
                return $fingerprint;
            }
        }

        return null;
    }
}
