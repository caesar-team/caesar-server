<?php

declare(strict_types=1);

namespace App\Limiter;

use App\Factory\Entity\SystemLimitFactory;
use App\Limiter\Model\LimitCheck;
use App\Repository\SystemLimitRepository;
use Psr\Log\LoggerInterface;

class Limiter implements LimiterInterface
{
    private SystemLimitRepository $repository;

    private SystemLimitFactory $factory;

    private LimiterInspectorRegistry $registry;

    private LoggerInterface $logger;

    public function __construct(
        SystemLimitRepository $repository,
        SystemLimitFactory $factory,
        LimiterInspectorRegistry $registry,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param LimitCheck[] $checkers
     */
    public function check(array $checkers): void
    {
        foreach ($checkers as $check) {
            /** @psalm-suppress DocblockTypeContradiction */
            if (!$check instanceof LimitCheck) {
                $this->logger->error(sprintf('[Limiter] $check is not implemented `%s` class', LimitCheck::class));
                continue;
            }

            try {
                $inspector = $this->registry->getInspector($check->getInspectorClass());
            } catch (\Exception $exception) {
                $this->logger->error(sprintf('[Limiter] Error: %s. Trace: %s', $exception->getMessage(), $exception->getTraceAsString()));
                continue;
            }
            $limit = $this->repository->getLimit($check->getInspectorClass());
            if (null === $limit) {
                $limit = $this->factory->createFromInspector($check->getInspectorClass());
                $this->repository->save($limit);
            }

            $inspector->inspect($limit, $check->getAddedSize());
        }
    }
}
