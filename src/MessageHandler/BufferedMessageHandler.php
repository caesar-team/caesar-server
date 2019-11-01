<?php

declare(strict_types=1);

namespace App\MessageHandler;
use App\Entity\Message\BufferedMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class BufferedMessageHandler implements MessageHandlerInterface
{
    public function __invoke(BufferedMessage $message)
    {
        dump('test'); die;
    }
}