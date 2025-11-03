<?php

declare(strict_types=1);

namespace Andante\NullableEmbeddableBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('andante_nullable_embeddable');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('metadata_cache_warmer_enabled')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
