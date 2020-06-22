<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;

interface ShareFactoryInterface
{
    /**
     * @todo candidate to refactoring
     *
     * @param mixed $data
     *
     * @return array<string, array<int, Item>> string as uid
     */
    public function share($data): array;

    /**
     * @param mixed $data
     */
    public function canShare($data): bool;
}
