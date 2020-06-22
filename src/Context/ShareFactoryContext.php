<?php

declare(strict_types=1);

namespace App\Context;

use App\Entity\Item;
use App\Strategy\ShareFactory\ShareFactoryInterface;

class ShareFactoryContext
{
    /**
     * @var ShareFactoryInterface[]
     */
    private $factories;

    public function __construct(ShareFactoryInterface ...$factories)
    {
        $this->factories = $factories;
    }

    /**
     * @param mixed $data
     *
     * @return array<string, array<int, Item>> string as uid
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
