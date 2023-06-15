<?php

namespace Islandora\Crayfish\Commons\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('crayfish_commons');
        $root = $treeBuilder->getRootNode();
        $root->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('fedora_base_uri')->cannotBeEmpty()->defaultValue('http://localhost:8080/fcrepo/rest')->end()
            ->booleanNode('apix_middleware_enabled')->defaultTrue()->end()
        ->end();

        return $treeBuilder;
    }
}
