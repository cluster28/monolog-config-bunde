<?php

namespace MonologConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package MonologConfigBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('monolog_config')
        ->children()
            ->arrayNode('sources')
            ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('files')
                        ->prototype('scalar')->end()
                        ->defaultValue(['https://raw.githubusercontent.com/cluster28/monolog-config-bundle/master/Resources/config/monolog_config.yml'])
                    ->end()
                    ->arrayNode('cdn')
                    ->end()
                ->end()
            ->end()
        ->end();
        return $treeBuilder;
    }
}