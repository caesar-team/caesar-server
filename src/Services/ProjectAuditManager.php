<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Billing\Audit;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class ProjectAuditManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Audit|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function scanApp(): ?Audit
    {
        $items = $this->entityManager->getRepository(Item::class)->findAll();

        $audit = $this->entityManager->getRepository(Audit::class)->findOneLatest();
        $audit->setMemoryUsed($this->calcSecretsSum($items));
        $audit->setUsersCount($this->calcUsersCount());
        $audit->setItemsCount($this->calcItemsCount());

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

    public function scanMemory(): void
    {
        $items = $this->entityManager->getRepository(Item::class)->findAll();
        $audit = $this->entityManager->getRepository(Audit::class)->findOneLatest();
        $audit->setMemoryUsed($this->calcSecretsSum($items));
    }
}