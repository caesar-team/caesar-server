<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Services\SecureMessageManager;
use PHPUnit\Framework\TestCase;
use App\Model\DTO\SecureMessage;
use Symfony\Component\Serializer\SerializerInterface;

class SecureMessageManagerTest extends TestCase
{
    /**
     * @var SecureMessageManager
     */
    private $secureMessageManager;

    /**
     * @throws \ReflectionException
     */
    protected function setUp(): void
    {
        /** @var \Redis $redis */
        $redis = $this->createMock(\Redis::class);
        /** @var SerializerInterface $serializer */
        $serializer = $this->createMock(SerializerInterface::class);
        $this->secureMessageManager = new SecureMessageManager($redis, $serializer);
    }

    public function testSave()
    {
        $message = new SecureMessage();
        $message->setMessage('test');
        $message->setRequestsLimit(1);
        $message->setSecondsLimit(80000);
        $this->secureMessageManager->save($message);

        $this->assertInstanceOf(SecureMessage::class, $this->secureMessageManager->get($message->getId()));
    }
}