<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;

interface ShareFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return array|Item[]
     */
    public function share($data): array;

    /**
     * @param mixed $data
     */
    public function canShare($data): bool;
}
