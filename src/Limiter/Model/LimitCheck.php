<?php

declare(strict_types=1);

namespace App\Limiter\Model;

class LimitCheck
{
    private string $inspectorClass;

    private int $addedSize;

    public function __construct(string $inspectorClass, int $addedSize)
    {
        $this->inspectorClass = $inspectorClass;
        $this->addedSize = $addedSize;
    }

    public function getInspectorClass(): string
    {
        return $this->inspectorClass;
    }

    public function getAddedSize(): int
    {
        return $this->addedSize;
    }
}
