<?php

declare(strict_types=1);

namespace App\Context;

use App\Entity\Item;
use App\Strategy\ShareFactory\ShareFactoryInterface;

final class ShareFactoryContext
{
    /**
     * @var array|ShareFactoryInterface[]
     */
    private $factories;

    public function __construct(ShareFactoryInterface ...$factories)
    {
        $this->factories = $factories;
    }

    /**
     * @param $data
     *
     * @return array|Item[]
     */
    public function share($data): array
    {
        foreach ($this->factories as $factory) {
            if (!$factory->canShare($data)) {
                continue;
            }

            return $factory->share($data);
        }

        return [];
    }
}