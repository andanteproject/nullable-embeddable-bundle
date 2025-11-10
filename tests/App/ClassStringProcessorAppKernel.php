<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App;

use Andante\NullableEmbeddableBundle\AndanteNullableEmbeddableBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClassStringProcessorAppKernel extends AppKernel
{
    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/class-string';
    }

    /**
     * @return iterable<int, Bundle>
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new AndanteNullableEmbeddableBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('doctrine', [
                'orm' => [
                    'mappings' => [
                        'AndanteNullableEmbeddableBundleValidClassStringProcessorProcessors' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Fixtures/ValidClassStringProcessorEntity',
                            'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity',
                            'alias' => 'ValidClassStringProcessorProcessorsMapping',
                        ],
                    ],
                ],
            ]);
        });
    }
}
