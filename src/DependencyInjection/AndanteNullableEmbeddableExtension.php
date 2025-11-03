<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\DependencyInjection;

use Andante\NullableEmbeddableBundle\Cache\Adapter\ArrayAdapter;
use Andante\NullableEmbeddableBundle\CacheClearer\NullableEmbeddableCacheClearer;
use Andante\NullableEmbeddableBundle\CacheWarmer\NullableEmbeddableCacheWarmer;
use Andante\NullableEmbeddableBundle\Doctrine\EventSubscriber\NullableEmbeddableSubscriber;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\MetadataFactory;
use Andante\NullableEmbeddableBundle\NullableEmbeddable\Registry;
use Andante\NullableEmbeddableBundle\ProcessorInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AndanteNullableEmbeddableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('andante_nullable_embeddable.metadata_cache_warmer_enabled', $config['metadata_cache_warmer_enabled']);

        $container->registerForAutoconfiguration(ProcessorInterface::class)
            ->addTag('andante_nullable_embeddable.processor');

        $metadataPhpArrayFile = '%kernel.cache_dir%/nullable_embeddable_metadata.php';

        $container->register(MetadataFactory::class, MetadataFactory::class)
            ->addArgument(new Reference('doctrine'));

        $container->register(ArrayAdapter::class, ArrayAdapter::class);

        $container->register(Registry::class, Registry::class)
            ->addArgument(new Reference(MetadataFactory::class))
            ->addArgument(new Definition(
                PhpArrayAdapter::class,
                [
                    $metadataPhpArrayFile,
                    new Reference(ArrayAdapter::class),
                ]
            ));

        $container->register(NullableEmbeddableSubscriber::class, NullableEmbeddableSubscriber::class)
            ->addArgument(new Reference(Registry::class))
            ->addTag('doctrine.event_listener', ['event' => 'postLoad']);

        $container->register(NullableEmbeddableCacheWarmer::class, NullableEmbeddableCacheWarmer::class)
            ->addArgument(new Reference(MetadataFactory::class))
            ->addArgument(new Reference('doctrine'))
            ->addArgument($metadataPhpArrayFile)
            ->addTag('kernel.cache_warmer');

        $container->register(NullableEmbeddableCacheClearer::class, NullableEmbeddableCacheClearer::class)
            ->addTag('kernel.cache_clearer');
    }
}
