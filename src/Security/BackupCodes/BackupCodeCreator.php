<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

use App\Entity\User;
use App\Repository\UserRepository;

class BackupCodeCreator
{
    private BackupCodesGeneratorInterface $generator;

    private BackupCodesEncoderInterface $encoder;

    private UserRepository $repository;

    public function __construct(
        BackupCodesGeneratorInterface $generator,
        BackupCodesEncoderInterface $encoder,
        UserRepository $repository
    ) {
        $this->generator = $generator;
        $this->encoder = $encoder;
        $this->repository = $repository;
    }

    public function createAndSaveBackupCodes(User $user): array
    {
        $codes = $this->generator->generate();

        return $this->saveBackupCodes($user, $codes);
    }

    public function saveBackupCodes(User $user, array $codes): array
    {
        $user->setBackupCodes($this->encoder->encode($codes));
        $this->repository->save($user);

        return $codes;
    }
}
