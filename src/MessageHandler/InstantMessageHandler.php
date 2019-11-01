<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Mailer\Sender\MailSender;
use App\Model\DTO\Message;
use App\Model\DTO\Message\InstantMessage;
use App\Repository\UserRepository;
use App\Services\Messenger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class InstantMessageHandler implements MessageHandlerInterface
{
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(Messenger $messenger, UserRepository $userRepository)
    {
        $this->messenger = $messenger;
        $this->userRepository = $userRepository;
    }

    /**
     * @param InstantMessage $message
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function __invoke(InstantMessage $message)
    {
        foreach ($message->getRecipients() as $recipient) {
            if (!$user = $this->userRepository->findOneByEmail($recipient)) {
                continue;
            }

            $messageDTO = new Message($user->getId()->toString(), $recipient, $message->getTemplate(), json_decode($message->getContent(), true));
            $this->messenger->send($user, $messageDTO);
        }
    }
}