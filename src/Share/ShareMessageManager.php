<?php

declare(strict_types=1);

namespace App\Share;

use App\Model\Message;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class ShareMessageManager
{
    public const PREFIX = 'messages';
    public const LIMIT_PREFIX = 'limits';

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
     * @param Message $message
     *
     * @return Message
     */
    public function create(Message $message)
    {
        $id = bin2hex(random_bytes(20));
        $message->setId($id);
        $message->setupExpiration();

        $redisId = $this->buildRedisId($message->getId());
        $limitId = $this->buildLimitId($message->getId());
        $this->redis->set($redisId, $this->serialize($message));
        $this->redis->expire($redisId, $message->getSecondsLimit());
        $this->redis->set($limitId, $message->getRequestsLimit());
        $this->redis->expire($limitId, $message->getSecondsLimit());

        return $message;
    }

    /**
     * @param string $id
     *
     * @return Message|null
     */
    public function get($id)
    {
        $json = $this->redis->get($this->buildRedisId($id));
        if (is_null($json)) {
            return null;
        }

        $message = $this->deserialize($json);

        $this->decreaseLimit($message);
        $this->deleteIfLastAttempt($message);

        return $message;
    }

    /**
     * @param Message $message
     *
     * @return int
     */
    protected function decreaseLimit(Message $message)
    {
        $res = $this->redis->decr($this->buildLimitId($message->getId()));

        return $res;
    }

    /**
     * Returns true if it is last attempt (based on requestsLimit), false - in opposite case.
     *
     * @param Message $message
     *
     * @return bool
     */
    protected function deleteIfLastAttempt(Message $message)
    {
        $limit = (int) $this->redis->get($this->buildLimitId($message->getId()));

        if (0 === $limit) {
            $this->redis->del($this->buildRedisId($message->getId()));
            $this->redis->del($this->buildLimitId($message->getId()));

            return true;
        }

        return false;
    }

    /**
     * @var string
     *
     * @return string
     */
    public function buildRedisId($id)
    {
        return $this::PREFIX.':'.$id;
    }

    /**
     * @var string
     *
     * @return string
     */
    public function buildLimitId($id)
    {
        return $this::LIMIT_PREFIX.':'.$id;
    }

    /**
     * @param Message $message
     *
     * @return string
     */
    public function serialize(Message $message)
    {
        return $this->serializer->serialize($message, 'json');
    }

    /**
     * @param string $data
     *
     * @return Message
     */
    public function deserialize($data)
    {
        return $this->serializer->deserialize($data, Message::class, 'json');
    }
}
