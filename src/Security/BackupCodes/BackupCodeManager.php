<?php

declare(strict_types=1);

namespace App\Security\BackupCodes;

use App\Entity\User;
use App\Repository\UserRepository;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;

class BackupCodeManager implements BackupCodeManagerInterface
{
    private UserRepository $repository;

    private BackupCodesEncoderInterface $encoder;

    public function __construct(
        BackupCodesEncoderInterface $encoder,
        UserRepository $repository
    ) {
        $this->encoder = $encoder;
        $this->repository = $repository;
    }

    public function isBackupCode($user, string $code): bool
    {
        if (!$user instanceof BackupCodeInterface) {
            return false;
        }

        return $user->isBackupCode($this->encoder->encode([$code])[0]);
    }

    public function invalidateBackupCode($user, string $code): void
    {
        if (!$user instanceof BackupCodeInterface) {
            return;
        }

        $user->invalidateBackupCode($this->encoder->encode([$code])[0]);
        if ($user instanceof User) {
            $this->repository->save($user);
        }
    }
}
