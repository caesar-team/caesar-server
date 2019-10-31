<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CaesarExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('env', [$this, 'getEnv']),
        ];
    }

    public function getEnv(string $varName)
    {
        return getenv($varName);
    }
}