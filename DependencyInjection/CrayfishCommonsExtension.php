<?php

namespace Islandora\Crayfish\Commons\DependencyInjection;

use Islandora\Chullo\IFedoraApi;
use Islandora\Crayfish\Commons\Client\GeminiClient;
use Islandora\Crayfish\Commons\CmdExecuteService;
use Islandora\Crayfish\Commons\Syn\JwtAuthenticator;
use Islandora\Crayfish\Commons\Syn\JwtFactory;
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
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('crayfish_commons.yaml');

        if (!$container->has('Islandora\Crayfish\Commons\Client\GeminiClient')) {
            $container->register('Islandora\Crayfish\Commons\Client\GeminiClient', GeminiClient::class)
              ->setFactory('Islandora\Crayfish\Commons\Client\GeminiClient::create')
              ->setArgument('$base_url', $config['gemini_base_uri']);
        }

        if (!$container->has('Islandora\Crayfish\Commons\Syn\SettingsParser') &&
            $config['syn_enabled'] === true) {
            if (file_exists($config['syn_config'])) {
                $xml = file_get_contents($config['syn_config']);
            } else {
                throw new IOException("Security configuration not found. ${config['syn_config']}");
            }

            $container->register('Islandora\Crayfish\Commons\Syn\SettingsParser', SettingsParser::class)
              ->setArgument('$xml', $xml);
        }
        if (!$container->has('Islandora\Chullo\IFedoraApi')) {
            $container->register('Islandora\Chullo\IFedoraApi', IFedoraApi::class)
                ->setFactory('Islandora\Chullo\FedoraApi::create')
                ->setArgument('$fedora_rest_url', $config['fedora_base_uri']);
            $container->setAlias('Islandora\Chullo\FedoraApi', 'Islandora\Chullo\IFedoraApi');
        }

        if (!$container->has('Islandora\Crayfish\Commons\ApixMiddleware')) {
            $container->registerForAutoconfiguration('Islandora\Crayfish\Commons\ApixMiddleware');
        }
    }
}
