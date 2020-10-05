<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\BackupCodes\BackupCodeCreator;
use App\Security\TwoFactor\BackUpCodesManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConvertBackupCodesCommand extends Command
{
    protected static $defaultName = 'app:backup-codes:convert';

    /** @var SymfonyStyle|null */
    private $io;

    private BackupCodeCreator $creator;

    private UserRepository $repository;

    public function __construct(UserRepository $repository, BackupCodeCreator $creator)
    {
        parent::__construct(null);
        $this->repository = $repository;
        $this->creator = $creator;
    }

    protected function configure()
    {
        $this->setDescription('Command coverts old backup codes format to new hash')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $encoder = BackUpCodesManager::initEncoder();
        $iterator = $this->repository->createQueryBuilder('user')->getQuery()->iterate();
        foreach ($iterator as [$user]) {
            if (!$user instanceof User) {
                continue;
            }

            $decodeCodes = [];
            foreach ($user->getBackupCodes() as $code) {
                $decodeCode = current($encoder->decode($code));
                if (false === $decodeCode) {
                    continue;
                }

                $decodeCodes[] = $decodeCode;
            }

            if (empty($decodeCodes)) {
                $this->io->text(sprintf('<comment> > User `%s` have empty or invalid codes, ignore...</comment>', $user->getUsername()));
                continue;
            }

            $this->io->text(sprintf('<info> > User `%s` converting codes</info>', $user->getUsername()));

            $this->creator->saveBackupCodes($user, $decodeCodes);
        }

        return 0;
    }
}
