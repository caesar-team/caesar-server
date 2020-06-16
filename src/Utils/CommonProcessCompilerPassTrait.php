<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

trait CommonProcessCompilerPassTrait
{
    private function registerProcess(ContainerBuilder $container, string $serviceClass, string $serviceTag)
    {
        $services = $container->findTaggedServiceIds($serviceTag);
        $serviceDefinition = $container->getDefinition($serviceClass);

        /**
         * @var string $serviceId
         * @var array  $tags
         */
        foreach ($services as $serviceId => $tags) {
            if ($serviceDefinition->getClass() === $serviceId) {
                continue;
            }

            $serviceDefinition->addArgument(new Reference($serviceId));
        }
    }
}
