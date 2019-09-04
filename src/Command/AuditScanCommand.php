<?php

namespace App\Command;

use App\Entity\Billing\Audit;
use App\Entity\Group;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\AuditRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AuditScanCommand extends Command
{
    protected static $defaultName = 'app:audit:scan';
    /**
     * @var AuditRepository
     */
    private $auditRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->auditRepository = $this->entityManager->getRepository(Audit::class);
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

        $result = $this->scanApp($io);

        $this->entityManager->flush();

        $io->success('Done!');
        $this->view($result, $io);
    }

    /**
     * @param SymfonyStyle $io
     * @return Audit|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function scanApp(SymfonyStyle $io): ?Audit
    {
        $items = $this->entityManager->getRepository(Item::class)->findAll();

        $audit = $this->auditRepository->findOneLatest();
         if (is_null($audit)) {
             $io->text('Audit record not found');

             return null;
         }
        $audit->setMemoryUsed($this->calcSecretsSum($items));
        $audit->setUsersCount($this->calcUsersCount());
        $audit->setItemsCount($this->calcItemsCount());
        //$audit->setTeamsCount($this->calcTeamsCount());

        return $audit;
    }

    /**
     * @param array|Item[] $items
     * @return int
     */
    private function calcSecretsSum(array $items): int
    {
        $secretsSymbols = array_map(function (Item $item) {
            return strlen($item->getSecret());
        }, $items);

        return array_sum($secretsSymbols);
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function calcUsersCount(): int
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->getCountCompleted();
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function calcItemsCount(): int
    {
        return $this->entityManager->getRepository(Item::class)->getCount();
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
