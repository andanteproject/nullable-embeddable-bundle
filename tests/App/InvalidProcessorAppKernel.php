<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InvalidProcessorAppKernel extends AppKernel
{
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('doctrine', [
                'orm' => [
                    'mappings' => [
                        'AndanteNullableEmbeddableBundleInvalid' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Fixtures/InvalidEntity',
                            'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Fixtures\InvalidEntity',
                            'alias' => 'InvalidEntityMapping',
                        ],
                    ],
                ],
            ]);
        });
    }
}
