<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Fingerprint;
use App\Security\Fingerprint\Exception\NotFoundFingerprintException;
use App\Security\Fingerprint\FingerprintExtractorInterface;
use App\Security\Fingerprint\FingerprintFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class FingerprintFactory implements FingerprintFactoryInterface
{
    private const DEFAULT_LIFETIME = 1209600; //Two weeks in seconds

    private FingerprintExtractorInterface $extractor;

    private int $lifetime;

    public function __construct(FingerprintExtractorInterface $extractor, int $lifetime = self::DEFAULT_LIFETIME)
    {
        $this->extractor = $extractor;
        $this->lifetime = $lifetime;
    }

    public function createFromRequest(Request $request): Fingerprint
    {
        $fingerprint = $this->extractor->extract($request);
        if (null === $fingerprint) {
            throw new NotFoundFingerprintException('Request has no fingerprint');
        }

        $entity = new Fingerprint();
        $entity->setExpiredAt(new \DateTimeImmutable(sprintf('+ %s seconds', $this->lifetime)));
        $entity->setLastIp($request->getClientIp());
        $entity->setClient($request->headers->get('User-Agent'));
        $entity->setFingerprint($fingerprint);

        return $entity;
    }
}
