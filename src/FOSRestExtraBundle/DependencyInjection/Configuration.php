<?php

namespace M6Web\Bundle\FOSRestExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for Controller Extra Bundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6_web_fos_rest_extra');

        $rootNode
            ->children()
                ->arrayNode('param_fetcher')
                    ->children()
                        ->scalarNode('allow_extra')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('strict')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('error_status_code')
                            ->defaultValue(400)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
