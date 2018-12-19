<?php

namespace App\Security\Guard;

use Symfony\Component\HttpFoundation\Request;

class TokenExtractor
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $name;

    public function __construct($prefix = 'Bearer', $name = 'Authorization')
    {
        $this->prefix = $prefix;
        $this->name = $name;
    }

    public function extract(Request $request)
    {
        if (!$request->headers->has($this->name)) {
            return false;
        }

        $authorizationHeader = $request->headers->get($this->name);

        if (empty($this->prefix)) {
            return $authorizationHeader;
        }

        $headerParts = explode(' ', $authorizationHeader);

        if (!(2 === count($headerParts) && 0 === strcasecmp($headerParts[0], $this->prefix))) {
            return false;
        }

        return $headerParts[1];
    }
}
