<?php

namespace App\Command;

use App\Model\DTO\Message;
use App\Repository\BufferedMessageRepository;
use App\Repository\UserRepository;
use App\Services\Messenger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailerSendCommand extends Command
{
    protected static $defaultName = 'app:mailer:send';
    /**
     * @var BufferedMessageRepository
     */
    private $bufferedMessageRepository;
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        BufferedMessageRepository $bufferedMessageRepository,
        Messenger $messenger,
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->bufferedMessageRepository = $bufferedMessageRepository;
        $this->messenger = $messenger;
        $this->userRepository = $userRepository;
    }


    protected function configure()
    {
        $this
            ->setDescription('Get all buffered messages and send to recipients')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $today = (new \DateTime('today'));

        $messages = $this->bufferedMessageRepository->findAllByDate($today);

        foreach ($messages as $message) {
            foreach ($message->getRecipients() as $recipient) {
                if (!$user = $this->userRepository->findOneByEmail($recipient)) {
                    continue;
                }
                $messageDTO = new Message($user->getId()->toString(), $recipient, $message->getTemplate(), json_decode($message->getContent(), true));
                $this->messenger->send($user, $messageDTO);
                $io->writeln(sprintf('Message sent to %s', $user->getId()->toString()));
            }
            $this->bufferedMessageRepository->remove($message);
        }
        $this->bufferedMessageRepository->flush();

        $io->success('Sending complete!');
    }
}
