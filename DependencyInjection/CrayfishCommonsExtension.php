<?php

namespace Islandora\Crayfish\Commons\DependencyInjection;

use Islandora\Chullo\IFedoraApi;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CrayfishCommonsExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(realpath(__DIR__ . '/../Resources/config'))
        );
        $loader->load('crayfish_commons.yaml');

        if (!$container->has('Islandora\Chullo\IFedoraApi')) {
            $container->register('Islandora\Chullo\IFedoraApi', IFedoraApi::class)
                ->setFactory('Islandora\Chullo\FedoraApi::create')
                ->setArgument('$fedora_rest_url', $config['fedora_base_uri']);
            $container->setAlias('Islandora\Chullo\FedoraApi', 'Islandora\Chullo\IFedoraApi');
        }
        if ($config['apix_middleware_enabled'] === false &&
            $container->has('Islandora\Crayfish\Commons\ApixMiddleware')) {
            $container->removeDefinition('Islandora\Crayfish\Commons\ApixMiddleware');
        }
    }
}
