<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClosureProcessorAppKernel extends AppKernel
{
    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/closure';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('doctrine', [
                'orm' => [
                    'mappings' => [
                        'AndanteNullableEmbeddableBundleValidClosureProcessorProcessors' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Fixtures/ValidClosureProcessorEntity',
                            'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClosureProcessorEntity',
                            'alias' => 'ValidClosureProcessorMapping',
                        ],
                    ],
                ],
            ]);
        });
    }
}
