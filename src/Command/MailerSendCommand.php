<?php

namespace App\Command;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\ShareLog;
use App\Entity\UpdateLog;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\DTO\Message;
use App\Repository\ItemRepository;
use App\Repository\ItemUpdateRepository;
use App\Repository\UserRepository;
use App\Services\Messenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailerSendCommand extends Command
{
    protected static $defaultName = 'app:mailer:send';
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ItemUpdateRepository
     */
    private $itemUpdateRepository;
    /**
     * @var ItemRepository
     */
    private $itemRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        Messenger $messenger,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->messenger = $messenger;
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->itemUpdateRepository = $this->entityManager->getRepository(ItemUpdate::class);
        $this->itemRepository = $this->entityManager->getRepository(Item::class);
    }


    protected function configure()
    {
        $this
            ->setDescription('Check offered items and updates and send notifications to recipients')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $items = [];
        $this->getUpdates($items);
        $this->getShares($items);
        $this->entityManager->flush();

        $this->sendMessages($items, $io);

        $io->success('Sending complete!');
    }

    private function getUpdates(array &$items): void
    {
        /** @var ItemUpdate[] $updates */
        $updates = $this->itemUpdateRepository->findAll();
        $updateLogRepository = $this->entityManager->getRepository(UpdateLog::class);
        foreach ($updates as $update) {
            if ($updateLog = $updateLogRepository->findOneBy(['update' =>$update ])) {
                continue;
            }
            $items[$update->getItem()->getSignedOwner()->getId()->toString()]['updates'][] = $update->getItem()->getId()->toString();
            $updateLog = new UpdateLog($update);
            $this->entityManager->persist($updateLog);
        }
    }

    private function getShares(array &$items): void
    {
        $shares = $this->itemRepository->findOfferedChildren();
        $shareLogRepository = $this->entityManager->getRepository(ShareLog::class);
        foreach ($shares as $share) {
            if ($shareLog = $shareLogRepository->findOneBy(['sharedItem' => $share])) {
                continue;
            }

            $items[$share->getSignedOwner()->getId()->toString()]['shares'][] = $share->getId()->toString();
            $shareLog = new ShareLog($share);
            $this->entityManager->persist($shareLog);
        }
    }

    private function sendMessages(array $items, SymfonyStyle $io): void
    {
        foreach ($items as $recipientId => $item) {
            /** @var User $user */
            if (!$user = $this->userRepository->find($recipientId)) {
                    continue;
            }
            $this->sendMessage($user, $item);
            $io->writeln(sprintf('Message sent to %s', $user->getId()->toString()));
        }
    }

    private function sendMessage(User $user, array $item): void
    {
        if (key_exists('shares', $item) && key_exists('updates', $item)) {
            $content = [
                'newItemsCount' => count($item['shares']),
                'updatedItemsCount' => count($item['updates']),
            ];

            $message = new Message($user->getId()->toString(), $user->getEmail(), MailRegistry::NEW_ITEMS_AND_UPDATES_MESSAGE, $content);
            $this->messenger->send($user, $message);

            return;
        }

        if (key_exists('shares', $item)) {
            $content = [
                'itemsCount' => count($item['shares']),
            ];
            $message = new Message($user->getId()->toString(), $user->getEmail(), MailRegistry::NEW_ITEM_MESSAGE, $content);
            $this->messenger->send($user, $message);

            return;
        }

        if (key_exists('updates', $item)) {
            $content = [
                'itemsCount' => count($item['updates']),
            ];
            $message = new Message($user->getId()->toString(), $user->getEmail(), MailRegistry::UPDATED_ITEM_MESSAGE, $content);
            $this->messenger->send($user, $message);
        }
    }
}
