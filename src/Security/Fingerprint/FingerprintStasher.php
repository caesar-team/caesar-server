<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class FingerprintStasher
{
    private const COOKIE_NAME = 'fingerprint';
    private const COOKIE_LIFETIME = 600;
    private const VALID_FINGERPRINT_LENGTH = 20;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function isValidFingerprint(string $fingerprint): bool
    {
        if (strlen($fingerprint) < self::VALID_FINGERPRINT_LENGTH) {
            return false;
        }

        return true;
    }

    public function stash(Response $response, string $fingerprint): void
    {
        if (self::isValidFingerprint($fingerprint)) {
            $response->headers->setCookie(new Cookie(self::COOKIE_NAME, $fingerprint, time() + self::COOKIE_LIFETIME));
        }
    }

    public function unstash(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        return null !== $request ? (string) $request->cookies->get(self::COOKIE_NAME) : null;
    }
}
