<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Context\ViewFactoryContext;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ViewFactoryContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, ViewFactoryContext::class, 'app.view_factory');
    }
}
