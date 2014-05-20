<?php

namespace AppVentus\DataMigrationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *
 * @author Thomas Beaujean <thomas@appventus.com>
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Get the config
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('app_ventus_data_migration');

        $rootNode
            ->children()
                ->scalarNode('migration_file_path')
                ->end()
                ->arrayNode('dumpable_entities')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('dumpable_instance_entities')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
