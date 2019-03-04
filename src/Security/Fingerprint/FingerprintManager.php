<?php

declare(strict_types=1);

namespace App\Security\Fingerprint;

use App\Entity\Fingerprint;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class FingerprintManager
{
    private const DEFAULT_LIFE_TIME = 1209600; //Two weeks in seconds

    /**
     * @var int
     */
    private $fingerprintLifeTime;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \DateTime
     */
    private $now;

    public function __construct(EntityManagerInterface $entityManager, int $fingerprintLifeTime = self::DEFAULT_LIFE_TIME)
    {
        $this->fingerprintLifeTime = $fingerprintLifeTime;
        $this->entityManager = $entityManager;
        $this->now = new \DateTime();
    }

    public function isHasFingerprint(User $user, string $fingerPrintString): bool
    {
        $repo = $this->entityManager->getRepository(Fingerprint::class);

        $fingerprint = $repo->findOneBy(['user' => $user, 'string' => $fingerPrintString]);

        return null !== $fingerprint;
    }

    public function rememberFingerprint(string $fingerPrintString, User $user): void
    {
        $this->invalidateOutdated($user);

        if (FingerprintStasher::isValidFingerprint($fingerPrintString)) {
            $fingerprint = new Fingerprint($user, $fingerPrintString);

            $this->entityManager->persist($fingerprint);
        }

        $this->entityManager->flush();
    }

    public function isValidDate(\DateTimeImmutable $dateTime): bool
    {
        $deadline = $dateTime->modify("+ {$this->fingerprintLifeTime}second");
        if ($deadline > $this->now) {
            return true;
        }

        return false;
    }

    private function invalidateOutdated(User $user)
    {
        /** @var Fingerprint $fingerprint */
        foreach ($user->getFingerprints() as $fingerprint) {
            if (false === $this->isValidDate($fingerprint->getCreatedAt())) {
                $user->removeFingerprint($fingerprint);
            }
        }

        $this->entityManager->persist($user);
    }

    public function findFingerPrintByUser($user): ?Fingerprint
    {
        $this->invalidateOutdated($user);
        $repo = $this->entityManager->getRepository(Fingerprint::class);

        return $repo->findOneBy(['user' => $user]);
    }
}
