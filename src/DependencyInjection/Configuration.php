<?php

namespace Opctim\CspBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private string $alias;


    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder($this->alias);

        $builder->getRootNode()
            ->children()

                ->arrayNode('always_add')
                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('report')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('url')->defaultNull()->end()
                        ->scalarNode('route')->defaultNull()->end()
                        ->arrayNode('route_params')
                            ->normalizeKeys(false)
                            ->arrayPrototype()
                                ->normalizeKeys(false)
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarPrototype()->end()
                        ->end()
                        ->integerNode('chance')->max(100)->min(0)->defaultValue(100)->end()
                    ->end()
                ->end()

                ->arrayNode('directives')
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->scalarPrototype()->end()
                    ->end()
                ->end()

            ->end();

        return $builder;
    }
}
