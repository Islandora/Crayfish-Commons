<?php

namespace Islandora\Crayfish\Commons\Provider;

use Islandora\Crayfish\Commons\CmdExecuteService;
use Islandora\Crayfish\Commons\FedoraResourceConverter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Islandora\Chullo\FedoraApi;
use Islandora\Crayfish\Commons\Syn\SettingsParser;
use Islandora\Crayfish\Commons\Syn\JwtAuthenticator;
use Islandora\Crayfish\Commons\Syn\JwtFactory;

class IslandoraServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $container)
    {
        // Register services we rely on
        $container->register(new MonologServiceProvider());
        $container->register(new ServiceControllerServiceProvider());
        $container->register(new SecurityServiceProvider());
        $container->register(new DoctrineServiceProvider());

        // Configure external services
        $container['monolog.logfile'] = function ($container) {
            return strtolower($container['crayfish.log.level']) == 'none' ? null : $container['crayfish.log.file'];
        };
        $container['monolog.level'] = function ($container) {
            return $container['crayfish.log.level'];
        };

        $container['security.firewalls'] = function ($container) {
            if ($container['crayfish.syn.enable']) {
                return [
                    'default' => [
                        'stateless' => true,
                        'anonymous' => false,
                        'guard' => [
                            'authenticators' => [
                                'crayfish.syn.jwt_authentication'
                            ],
                        ],
                    ],
                ];
            } else {
                return [];
            }
        };

        $container['db.options'] = function ($container) {
            $settings = [];
            if (isset($container['crayfish.db.driver'])) {
                $settings['driver'] = $container['crayfish.db.driver'];
            }
            if (isset($container['crayfish.db.host'])) {
                $settings['host'] = $container['crayfish.db.host'];
            }
            if (isset($container['crayfish.db.port'])) {
                $settings['port'] = $container['crayfish.db.port'];
            }
            if (isset($container['crayfish.db.dbname'])) {
                $settings['dbname'] = $container['crayfish.db.dbname'];
            }
            if (isset($container['crayfish.db.user'])) {
                $settings['user'] = $container['crayfish.db.user'];
            }
            if (isset($container['crayfish.db.password'])) {
                $settings['password'] = $container['crayfish.db.password'];
            }
            if (isset($container['crayfish.db.charset'])) {
                $settings['charset'] = $container['crayfish.db.charset'];
            }
            if (isset($container['crayfish.db.path'])) {
                $settings['path'] = $container['crayfish.db.path'];
            }
            return $settings;
        };

        // Register our services
        $container['crayfish.cmd_execute_service'] = function ($container) {
            return new CmdExecuteService(
                $container['monolog']->withName('crayfish.cmd_execute_service')
            );
        };

        $container['crayfish.fedora_resource'] = function ($container) {
            return new FedoraResourceConverter(
                FedoraApi::create($container['crayfish.fedora_resource.base_url'])
            );
        };

        $container['crayfish.syn.settings_parser'] = function ($container) {
            if (file_exists($container['crayfish.syn.config'])) {
                $xml = file_get_contents($container['crayfish.syn.config']);
            } else {
                $xml = '';
                $container['monolog']
                    ->error("Securty configuration not found. ${container['crayfish.syn.config']}");
            }

            return new SettingsParser(
                $xml,
                $container['monolog']->withName('crayfish.syn.settings_parser')
            );
        };

        $container['crayfish.syn.jwt_authentication'] = function ($app) {
            return new JwtAuthenticator(
                $app['crayfish.syn.settings_parser'],
                new JwtFactory(),
                $app['monolog']->withName('crayfish.syn.jwt_authentication')
            );
        };
    }
}
