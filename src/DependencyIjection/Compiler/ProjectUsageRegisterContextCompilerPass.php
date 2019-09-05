<?php

declare(strict_types=1);

namespace App\DependencyIjection\Compiler;

use App\Context\ProjectUsageRegisterContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ProjectUsageRegisterContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, ProjectUsageRegisterContext::class, 'app.project_usage_register');
    }
}