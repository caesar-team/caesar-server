<?php

namespace App\Services\SecurityMessage;

use Caesar\SecurityMessageBundle\Service\ClientInterface;
use Redis;

class Client implements ClientInterface
{
    /**
     * @var Redis
     */
    private $client;

    public function __construct(Redis $client)
    {
        $this->client = $client;
    }

    public function set(string $key, $value, int $timeout = null): bool
    {
        return $this->client->set($key, $value, $timeout);
    }

    public function get(string $key)
    {
        return $this->client->get($key);
    }

    public function expire(string $key, int $ttl): bool
    {
        return $this->client->expire($key, $ttl);
    }

    public function del(string $key, ...$otherKeys): int
    {
        return $this->client->del($key, ...$otherKeys);
    }

    public function decr(string $key): int
    {
        return $this->client->decr($key);
    }

    public function ttl(string $key)
    {
        return $this->client->ttl($key);
    }
}
