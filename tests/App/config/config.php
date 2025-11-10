<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('kernel.secret', 'test_secret');
    $container->extension('framework', [
        'secret' => '%kernel.secret%',
        'test' => true,
        'router' => [
            'resource' => 'kernel::loadRoutes',
            'type' => 'service',
        ],
    ]);

    $container->extension('doctrine', [
        'dbal' => [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ],
        'orm' => [
            'auto_mapping' => true,
            'mappings' => [
                'AndanteNullableEmbeddableBundle' => [
                    'is_bundle' => true,
                    'type' => 'attribute',
                    'dir' => '../tests/Fixtures/ValidClassStringProcessorEntity',
                    'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidClassStringProcessorEntity',
                    'alias' => 'App',
                ],
            ],
        ],
    ]);

    // Conditionally configure Doctrine ORM for native lazy objects based on Symfony version
    if (Kernel::VERSION_ID >= 60000) { // Symfony 6.0 or higher
        $container->extension('doctrine', [
            'orm' => [
                'auto_generate_proxy_classes' => false,
                'enable_lazy_ghost_objects' => true,
                'proxy_dir' => '%kernel.cache_dir%/doctrine/Proxies',
                'proxy_namespace' => 'Proxies',
            ],
        ]);
    } else {
        // For older Symfony versions, keep proxy generation enabled
        $container->extension('doctrine', [
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'proxy_dir' => '%kernel.cache_dir%/doctrine/Proxies',
                'proxy_namespace' => 'Proxies',
            ],
        ]);
    }
};
