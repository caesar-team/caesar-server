<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FrontendUriHandler
{
    private const COOKIE_NAME = 'frontend_uri';
    private const COOKIE_LIFETIME = 600;

    /**
     * @var array
     */
    private $validUriCollection;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack, array $validUriCollection)
    {
        $this->requestStack = $requestStack;
        $this->validUriCollection = $validUriCollection;
    }

    public function validateUri($uri): bool
    {
        if (empty($uri)) {
            return  true;
        }

        $redirectUri = parse_url($uri);
        $redirectUri = $redirectUri['host'].(isset($redirectUri['port']) ? ':'.$redirectUri['port'] : '');
        if ($this->requestStack->getCurrentRequest()->getHttpHost() === $redirectUri) {
            return true;
        }

        foreach ($this->validUriCollection as $validUri) {
            if (preg_match($validUri, $uri)) {
                return true;
            }
        }

        throw new BadRequestHttpException(sprintf('Frontend uri "%s" not in valid uri scope', $uri));
    }

    public function persistUri(Response $response, string $uri): void
    {
        $this->validateUri($uri);
        $response->headers->setCookie(new Cookie(self::COOKIE_NAME, $uri, time() + self::COOKIE_LIFETIME));
    }

    public function extractUri(Request $request): ?string
    {
        $uri = $request->cookies->get(self::COOKIE_NAME);
        $this->validateUri($uri);

        return $uri;
    }
}
