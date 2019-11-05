<?php

declare(strict_types=1);

namespace App\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearMessageHistoryCommand extends Command
{
    protected static $defaultName = 'app:clear-message-history';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Clear old data from buffered_message table')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeDataOlderThan(new \DateTimeImmutable());
    }

    /**
     * @param \DateTimeImmutable $data
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeDataOlderThan(\DateTimeImmutable $data)
    {
        $sql = "DELETE FROM notification_log WHERE notification_log.created_at <:dateString";
        $dateString = $data->format('Y-m-d 00:00:00');
        $params = ['dateString' => $dateString];

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute($params);
    }

}