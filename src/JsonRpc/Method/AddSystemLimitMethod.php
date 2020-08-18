<?php

declare(strict_types=1);

namespace App\JsonRpc\Method;

use App\Factory\Entity\SystemLimitFactory;
use App\Limiter\Inspector\DatabaseSizeInspector;
use App\Limiter\Inspector\ItemCountInspector;
use App\Limiter\Inspector\TeamCountInspector;
use App\Limiter\Inspector\UserCountInspector;
use App\Repository\SystemLimitRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Yoanm\JsonRpcParamsSymfonyValidator\Domain\MethodWithValidatedParamsInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class AddSystemLimitMethod implements JsonRpcMethodInterface, MethodWithValidatedParamsInterface
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
        if (!isset($paramList['inspector']) || !isset($paramList['limit'])) {
            throw new BadRequestHttpException();
        }

        $systemLimit = $this->repository->getLimit($paramList['inspector']);
        if (null === $systemLimit) {
            $systemLimit = $this->factory->createFromInspector($paramList['inspector']);
        }

        $systemLimit->addLimitSize((int) $paramList['limit']);
        $this->repository->save($systemLimit);

        return $systemLimit->toArray();
    }

    public function getParamsConstraint(): Constraint
    {
        return new Collection(['fields' => [
            'inspector' => new Required([
                new Choice(self::AVAILABLE_INSPECTORS),
            ]),
            'limit' => new Required([
                new NotBlank(),
            ]),
        ]]);
    }
}
