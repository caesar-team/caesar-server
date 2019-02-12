<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\DTO\SecureMessage;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class SecureMessageManager
{
    public const PREFIX = 'messages';
    public const LIMIT_PREFIX = 'limits';
    public const UNLIMITED_VALUE = -1;

    /**
     * @var Client
     */
    private $redis;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(\Redis $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    public function save(SecureMessage $message): SecureMessage
    {
        $redisId = $this->buildRedisId($message->getId());
        $limitId = $this->buildLimitId($message->getId());
        $this->redis->set($redisId, $this->serialize($message));
        $this->redis->set($limitId, $message->getRequestsLimit());

        if (self::UNLIMITED_VALUE !== $message->getSecondsLimit()) {
            $this->redis->expire($redisId, $message->getSecondsLimit());
            $this->redis->expire($limitId, $message->getSecondsLimit());
        }

        return $message;
    }

    public function has(string $id): bool
    {
        $json = $this->redis->get($this->buildRedisId($id));

        return null !== $json;
    }

    public function get($id): ?SecureMessage
    {
        $redisId = $this->buildRedisId($id);

        $rawMessage = $this->redis->get($redisId);
        if (false === $rawMessage) {
            return null;
        }

        $ttl = $this->redis->ttl($redisId);
        $attemptsLeft = (int) $this->redis->get($this->buildLimitId($id));
        $message = $this->deserialize($id, $rawMessage, $ttl, $attemptsLeft);

        if (self::UNLIMITED_VALUE !== $message->getRequestsLimit()) {
            $this->decreaseLimit($message);
            $this->deleteIfLastAttempt($message);
        }

        return $message;
    }

    public function buildRedisId(string $id): string
    {
        return $this::PREFIX.':'.$id;
    }

    public function buildLimitId(string $id): string
    {
        return $this::LIMIT_PREFIX.':'.$id;
    }

    public function serialize(SecureMessage $message): string
    {
        return $message->getMessage();
    }

    public function deserialize(string $id, string $data, int $ttl, int $attemptsLeft): SecureMessage
    {
        $message = new SecureMessage();

        $message->setId($id);
        $message->setMessage($data);
        $message->setSecondsLimit($ttl);
        $message->setRequestsLimit($attemptsLeft);

        return $message;
    }

    protected function decreaseLimit(SecureMessage $message): int
    {
        $res = $this->redis->decr($this->buildLimitId($message->getId()));

        return $res;
    }

    /**
     * Returns true if it is last attempt (based on requestsLimit), false - in opposite case.
     *
     * @param SecureMessage $message
     *
     * @return bool
     */
    protected function deleteIfLastAttempt(SecureMessage $message): bool
    {
        if (1 >= $message->getRequestsLimit()) {
            $this->redis->del($this->buildRedisId($message->getId()));
            $this->redis->del($this->buildLimitId($message->getId()));

            return true;
        }

        return false;
    }
}
