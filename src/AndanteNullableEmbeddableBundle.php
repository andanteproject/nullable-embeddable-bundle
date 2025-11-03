<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle;

use Andante\NullableEmbeddableBundle\DependencyInjection\Compiler\CacheWarmerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AndanteNullableEmbeddableBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new CacheWarmerCompilerPass());
    }
}
