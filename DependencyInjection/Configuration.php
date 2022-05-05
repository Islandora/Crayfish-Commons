<?php

namespace Islandora\Crayfish\Commons\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('crayfish_commons');
        $root = $treeBuilder->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('fedora_base_uri')->cannotBeEmpty()->defaultValue('http://localhost:8080/fcrepo/rest')->end()
            ->scalarNode('syn_config')->defaultValue(__DIR__ . '/../Resources/default_syn.xml')->end()
        ->end();

        return $treeBuilder;
    }
}
