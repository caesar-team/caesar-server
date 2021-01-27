<?php

declare(strict_types=1);

namespace App\Factory\View;

use App\Entity\Config;

class ConfigViewFactory
{
    /**
     * @param Config[] $configs
     *
     * @return array<string, string|null>
     */
    public function createCollection(array $configs): array
    {
        $view = [];
        foreach ($configs as $config) {
            $view[$config->getKey()] = $config->getValue();
        }

        return $view;
    }
}
