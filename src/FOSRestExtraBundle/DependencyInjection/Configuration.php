<?php
namespace M6Web\Bundle\FOSRestExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ->arrayNode('extra_query_parameters')
                    ->children()
                        ->scalarNode('always_check')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('http_code')
                            ->defaultValue(400)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
