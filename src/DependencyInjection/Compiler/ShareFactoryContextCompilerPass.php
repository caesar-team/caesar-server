<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Context\ShareFactoryContext;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ShareFactoryContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, ShareFactoryContext::class, 'app.share_factory');
    }
}
