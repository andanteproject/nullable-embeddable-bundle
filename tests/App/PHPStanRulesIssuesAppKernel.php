<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\Tests\App;

use Andante\NullableEmbeddableBundle\AndanteNullableEmbeddableBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PHPStanRulesIssuesAppKernel extends AppKernel
{
    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/phpstan-rules-issues';
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
                        'PHPStanRulesIssuesTests' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Functional/PHPStanRulesIssuesTests/Fixtures',
                            'prefix' => 'Andante\NullableEmbeddableBundle\Tests\Functional\PHPStanRulesIssuesTests\Fixtures',
                            'alias' => 'PHPStanRulesIssuesMapping',
                        ],
                    ],
                ],
            ]);
        });
    }
}
