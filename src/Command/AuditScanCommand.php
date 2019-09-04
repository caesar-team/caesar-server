<?php

namespace App\Command;

use App\Entity\Billing\Audit;
use App\Repository\AuditRepository;
use App\Services\ProjectAuditManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuditScanCommand extends Command
{
    protected static $defaultName = 'app:audit:scan';

    /**
     * @var ProjectAuditManager
     */
    private $auditManager;

    /**
     * @var AuditRepository
     */
    private $auditRepository;

    public function __construct(AuditRepository $auditRepository, ProjectAuditManager $auditManager)
    {
        parent::__construct();
        $this->auditManager = $auditManager;
        $this->auditRepository = $auditRepository;
    }


    protected function configure()
    {
        $this
            ->setDescription('Scan current limits')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->auditManager->scanApp();
        if (is_null($result)) {
            $io->text('Audit record not found');

            return null;
        }
        $this->auditRepository->save($result);

        $io->success('Done!');
        $this->view($result, $io);
    }

    private function view(Audit $audit, SymfonyStyle $io)
    {
        $headers = ['id', 'type', 'users', 'items', 'memory used'];
        $row = [
            $audit->getId()->toString(),
            $audit->getBillingType(),
            $audit->getUsersCount(),
            $audit->getItemsCount(),
            $audit->getMemoryUsed(),
        ];

        $io->table($headers, [$row]);
    }
}
