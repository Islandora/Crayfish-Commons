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
            ->scalarNode('gemini_base_uri')->cannotBeEmpty()->defaultValue('http://localhost:8000/gemini')->end()
            ->scalarNode('syn_config')->defaultValue('')->end()
            ->booleanNode('syn_enabled')->defaultTrue()->end()
        ->end();

        return $treeBuilder;
    }
}
