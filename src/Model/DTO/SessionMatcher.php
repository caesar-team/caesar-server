<?php

declare(strict_types=1);

namespace App\Model\DTO;

class SessionMatcher
{
    /**
     * @var string
     */
    protected $serverSession;

    /**
     * @var string
     */
    protected $matcher;

    public function __construct(string $serverSession, string $matcher)
    {
        $this->serverSession = $serverSession;
        $this->matcher = $matcher;
    }

    public function getMatcher(): string
    {
        return $this->matcher;
    }

    public function getServerSession(): string
    {
        return $this->serverSession;
    }
}
