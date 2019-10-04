<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Context\ViewFactoryContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Utils\CommonProcessCompilerPassTrait;

final class ViewFactoryContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, ViewFactoryContext::class, 'app.view_factory');
    }
}