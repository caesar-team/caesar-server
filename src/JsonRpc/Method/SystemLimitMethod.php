<?php

declare(strict_types=1);

namespace App\JsonRpc\Method;

use App\Entity\SystemLimit;
use App\Factory\Entity\SystemLimitFactory;
use App\Limiter\Inspector\DatabaseSizeInspector;
use App\Limiter\Inspector\ItemCountInspector;
use App\Limiter\Inspector\TeamCountInspector;
use App\Limiter\Inspector\UserCountInspector;
use App\Repository\SystemLimitRepository;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class SystemLimitMethod implements JsonRpcMethodInterface
{
    private const AVAILABLE_INSPECTORS = [
        DatabaseSizeInspector::class,
        ItemCountInspector::class,
        TeamCountInspector::class,
        UserCountInspector::class,
    ];

    private SystemLimitRepository $repository;

    private SystemLimitFactory $factory;

    public function __construct(SystemLimitRepository $repository, SystemLimitFactory $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function apply(array $paramList = null)
    {
        $systemLimits = $this->repository->getLimitsWithIndexAlias(self::AVAILABLE_INSPECTORS);
        foreach (self::AVAILABLE_INSPECTORS as $inspector) {
            if (isset($systemLimits[$inspector])) {
                continue;
            }

            $systemLimit = $this->factory->createFromInspector($inspector);
            $this->repository->save($systemLimit);

            $systemLimits[$inspector] = $systemLimit;
        }

        return array_values(array_map(static function (SystemLimit $limit) {
            return $limit->toArray();
        }, $systemLimits));
    }
}
