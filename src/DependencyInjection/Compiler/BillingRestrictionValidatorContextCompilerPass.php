<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Context\BillingRestrictionValidatorContext;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class BillingRestrictionValidatorContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, BillingRestrictionValidatorContext::class, 'app.billing_validator');
    }
}