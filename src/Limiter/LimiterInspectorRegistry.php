<?php

declare(strict_types=1);

namespace App\Limiter;

use App\Limiter\Inspector\InspectorInterface;

class LimiterInspectorRegistry
{
    /**
     * @var InspectorInterface[]
     */
    private array $inspectors;

    public function __construct(InspectorInterface ...$inspectors)
    {
        foreach ($inspectors as $inspector) {
            $this->inspectors[get_class($inspector)] = $inspector;
        }
    }

    public function getInspector(string $inspectorClass): InspectorInterface
    {
        if (!isset($this->inspectors[$inspectorClass])) {
            throw new \InvalidArgumentException(sprintf('Inspector %s does not exists.', $inspectorClass));
        }

        return $this->inspectors[$inspectorClass];
    }
}
