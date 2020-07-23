<?php

namespace App\Command;

use App\Entity\MessageLog;
use App\Notification\MessageGrouper\MessageGrouperInterface;
use App\Notification\MessengerInterface;
use App\Repository\MessageLogRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailerDeferredSendCommand extends Command
{
    protected static $defaultName = 'app:mailer:deferred:send';

    /** @var SymfonyStyle|null */
    private $io;

    private MessageLogRepository $repository;

    private MessageGrouperInterface $messageGrouper;

    private MessengerInterface $messenger;

    public function __construct(
        MessageLogRepository $repository,
        MessageGrouperInterface $messageGrouper,
        MessengerInterface $messenger
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->messageGrouper = $messageGrouper;
        $this->messenger = $messenger;
    }

    protected function configure()
    {
        $this->setDescription('Check deferred emails and send to recipients');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messages = $this->repository->getLatestDeferredMessages();

        $groupMessages = $this->groupByRecipientAndEvent($messages);
        foreach ($groupMessages as $recipient => $events) {
            foreach ($this->messageGrouper->group($events) as $message) {
                $this->messenger->send($message);
            }
        }

        $this->repository->markAsSentMessages($messages);
        $this->io->success('Sending complete!');

        return 0;
    }

    private function groupByRecipientAndEvent(array $messages): array
    {
        $group = [];
        /** @var MessageLog $message */
        foreach ($messages as $message) {
            $group[$message->getRecipient()][$message->getEvent()][] = $message;
        }

        return $group;
    }
}
