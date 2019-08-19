<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Factory\Validator\FidoResponseValidatorFactory;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FidoResponseValidatorFactoryCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, FidoResponseValidatorFactory::class, 'fido.response_validator');
    }
}