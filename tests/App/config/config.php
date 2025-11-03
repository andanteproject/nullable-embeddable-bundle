<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
        'session' => [
            'storage_factory_id' => 'session.storage.factory.mock_file',
        ],
        'property_info' => [
            'with_constructor_extractor' => true,
        ],
    ]);

    $container->extension('doctrine', [
        'dbal' => [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'auto_mapping' => true,
            'mappings' => [
                'AndanteNullableEmbeddableBundle' => [
                    'is_bundle' => true,
                    'type' => 'attribute',
                    'dir' => '../tests/Fixtures/ValidEntity',
                    'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Fixtures\ValidEntity',
                    'alias' => 'App',
                ],
            ],
        ],
    ]);
};
