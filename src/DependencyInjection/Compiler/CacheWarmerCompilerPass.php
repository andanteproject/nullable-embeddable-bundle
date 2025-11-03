<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\DependencyInjection\Compiler;

use Andante\NullableEmbeddableBundle\CacheWarmer\NullableEmbeddableCacheWarmer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheWarmerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('andante_nullable_embeddable.metadata_cache_warmer_enabled') || !$container->getParameter('andante_nullable_embeddable.metadata_cache_warmer_enabled')) {
            $definition = $container->getDefinition(NullableEmbeddableCacheWarmer::class);
            $definition->clearTag('kernel.cache_warmer');
        }
    }
}
