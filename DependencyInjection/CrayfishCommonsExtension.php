<?php

namespace Islandora\Crayfish\Commons\DependencyInjection;

use Islandora\Chullo\IFedoraApi;
use Islandora\Crayfish\Commons\Client\GeminiClient;
use Islandora\Crayfish\Commons\CmdExecuteService;
use Islandora\Crayfish\Commons\EntityMapper\EntityMapper;
use Islandora\Crayfish\Commons\Syn\JwtAuthenticator;
use Islandora\Crayfish\Commons\Syn\JwtFactory;
use Islandora\Crayfish\Commons\Syn\JwtUserProvider;
use Islandora\Crayfish\Commons\Syn\SettingsParser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Exception\IOException;
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

        if (!$container->has('Islandora\Crayfish\Commons\Syn\SettingsParser')) {
            if (file_exists($config['syn_config'])) {
                $xml = file_get_contents($config['syn_config']);
            }
            else {
                throw new IOException("Security configuration not found. ${config['syn_config']}");
            }

            $container->register('Islandora\Crayfish\Commons\Syn\SettingsParser', SettingsParser::class)
                ->setArgument('$xml', $xml);
        }

        if (!$container->has('Islandora\Crayfish\Commons\Syn\JwtUserProvider')) {
            $container->register('Islandora\Crayfish\Commons\Syn\JwtUserProvider', JwtUserProvider::class);
        }
        if (!$container->has('Islandora\Crayfish\Commons\Syn\JwtFactory')) {
            $container->register('Islandora\Crayfish\Commons\Syn\JwtFactory', JwtFactory::class);
        }
        if (!$container->has('Islandora\Crayfish\Commons\Syn\JwtAuthenticator')) {
            $container->register('Islandora\Crayfish\Commons\Syn\JwtAuthenticator', JwtAuthenticator::class)
            ->setAutowired(true);
        }

        if (!$container->has('Islandora\Chullo\IFedoraApi')) {
            $container->register('Islandora\Chullo\IFedoraApi', IFedoraApi::class)
                ->setFactory('Islandora\Chullo\FedoraApi::create')
                ->setArgument('$fedora_rest_url', $config['fedora_base_uri']);
            $container->setAlias('Islandora\Chullo\FedoraApi', 'Islandora\Chullo\IFedoraApi');
        }
    }
}
