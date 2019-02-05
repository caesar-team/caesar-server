<?php

declare(strict_types=1);

namespace App\Share;

use App\Model\DTO\ShareMessage;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class ShareMessageManager
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

    public function __construct(Client $redis, SerializerInterface $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    /**
     * @param ShareMessage $message
     *
     * @return ShareMessage
     */
    public function save(ShareMessage $message): ShareMessage
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

    /**
     * @param string $id
     *
     * @return ShareMessage|null
     */
    public function get($id): ?ShareMessage
    {
        $json = $this->redis->get($this->buildRedisId($id));
        if (is_null($json)) {
            return null;
        }

        $message = $this->deserialize($json);

        if (self::UNLIMITED_VALUE !== $message->getRequestsLimit()) {
            $this->decreaseLimit($message);
            $this->deleteIfLastAttempt($message);
        }

        $message->setSecondsLimit($this->redis->ttl($this->buildRedisId($id)));

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

    /**
     * @param ShareMessage $message
     *
     * @return string
     */
    public function serialize(ShareMessage $message): string
    {
        return $this->serializer->serialize($message, 'json');
    }

    /**
     * @param string $data
     *
     * @return ShareMessage
     */
    public function deserialize($data)
    {
        return $this->serializer->deserialize($data, ShareMessage::class, 'json');
    }

    /**
     * @param ShareMessage $message
     *
     * @return int
     */
    protected function decreaseLimit(ShareMessage $message)
    {
        $res = $this->redis->decr($this->buildLimitId($message->getId()));

        return $res;
    }

    /**
     * Returns true if it is last attempt (based on requestsLimit), false - in opposite case.
     */
    protected function deleteIfLastAttempt(ShareMessage $message): bool
    {
        $limit = (int) $this->redis->get($this->buildLimitId($message->getId()));

        if (0 === $limit) {
            $this->redis->del($this->buildRedisId($message->getId()));
            $this->redis->del($this->buildLimitId($message->getId()));

            return true;
        }

        return false;
    }
}
