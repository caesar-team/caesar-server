<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Webauthn\PublicKeyCredentialOptionsContext;
use App\Utils\CommonProcessCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CredentialOptionsContextCompilerPass implements CompilerPassInterface
{
    use CommonProcessCompilerPassTrait;

    public function process(ContainerBuilder $container)
    {
        $this->registerProcess($container, PublicKeyCredentialOptionsContext::class, 'webauthn.options_builder');
    }
}
