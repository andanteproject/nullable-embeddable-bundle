<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App\Compiler;

use Andante\NullableEmbeddableBundle\Cache\Adapter\ArrayAdapter;
use Andante\NullableEmbeddableBundle\CacheClearer\NullableEmbeddableCacheClearer;
use Andante\NullableEmbeddableBundle\CacheWarmer\NullableEmbeddableCacheWarmer;
use Andante\NullableEmbeddableBundle\Doctrine\EventSubscriber\NullableEmbeddableSubscriber;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Registry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(ArrayAdapter::class)) {
            $container->getDefinition(ArrayAdapter::class)->setPublic(true);
        }
        if ($container->hasDefinition(Registry::class)) {
            $container->getDefinition(Registry::class)->setPublic(true);
        }
        if ($container->hasDefinition(NullableEmbeddableSubscriber::class)) {
            $container->getDefinition(NullableEmbeddableSubscriber::class)->setPublic(true);
        }
        if ($container->hasDefinition(NullableEmbeddableCacheWarmer::class)) {
            $container->getDefinition(NullableEmbeddableCacheWarmer::class)->setPublic(true);
        }
        if ($container->hasDefinition(NullableEmbeddableCacheClearer::class)) {
            $container->getDefinition(NullableEmbeddableCacheClearer::class)->setPublic(true);
        }
    }
}
