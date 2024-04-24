<?php

namespace Opctim\CspBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function __construct(private string $alias)
    {}

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder($this->alias);

        $builder->getRootNode()
            ->children()

                ->arrayNode('always_add')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('directives')
                    ->arrayPrototype()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()

            ->end();

        return $builder;
    }
}
