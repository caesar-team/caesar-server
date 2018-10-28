<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontendUriHandler
{
    private const COOKIE_NAME = 'frontend_uri';
    private const COOKIE_LIFETIME = 600;

    /**
     * @var array
     */
    private $validUriCollection;

    public function __construct(array $validUriCollection)
    {
        $this->validUriCollection = $validUriCollection;
    }

    public function validateUri($uri): bool
    {
        if (empty($uri)) {
            throw new NotFoundHttpException('Empty frontend uri');
        }

        foreach ($this->validUriCollection as $validUri) {
            if (preg_match($validUri, $uri)) {
                return true;
            }
        }

        throw new BadRequestHttpException('Frontend uri not in valid uri scope');
    }

    public function persistUri(Response $response, string $uri)
    {
        $this->validateUri($uri);
        $response->headers->setCookie(new Cookie(self::COOKIE_NAME, $uri, time() + self::COOKIE_LIFETIME));
    }

    public function extractUri(Request $request): string
    {
        $uri = $request->cookies->get(self::COOKIE_NAME);
        $this->validateUri($uri);

        return $uri;
    }
}
