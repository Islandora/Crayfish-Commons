<?php

namespace Islandora\Crayfish\Commons;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Islandora\Chullo\FedoraApi;
use Islandora\Crayfish\Commons\Syn\SettingsParser;
use Islandora\Crayfish\Commons\Syn\JwtAuthenticator;
use Islandora\Crayfish\Commons\Syn\JwtFactory;

class IslandoraServiceProvider implements ServiceProviderInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $config = $this->config;

        // Only use logger if configured
        if (strtolower($config['loglevel']) === 'none') {
            $app['monolog'] = function () {
                return new Logger('null', [new NullHandler()]);
            };
        } else {
            $app->register(new MonologServiceProvider(), [
                'monolog.logfile' => $config['logfile'],
                'monolog.level' => $config['loglevel'],
                'monolog.name' => 'Houdini',
            ]);
        }

        $app->register(new ServiceControllerServiceProvider());

        $app['crayfish.cmd_execute_service'] = function ($app) {
            return new CmdExecuteService(
                $app['monolog']->withName('crayfish.cmd_execute_service')
            );
        };

        $app['crayfish.fedora_resource'] = function () use ($config) {
            return new FedoraResourceConverter(
                FedoraApi::create($config['fedora base url'])
            );
        };

        $app['crayfish.syn.settings_parser'] = function ($app) use ($config) {
            $xml = file_get_contents($config['security config']);
            return new SettingsParser(
                $xml,
                $app['monolog']->withName('crayfish.syn.settings_parser')
            );
        };

        $app['crayfish.syn.jwt_authentication'] = function ($app) {
            return new JwtAuthenticator(
                $app['crayfish.syn.settings_parser'],
                new JwtFactory(),
                $app['monolog']->withName('crayfish.syn.jwt_authentication')
            );
        };

        if ($config['security enabled']) {
            $app->register(new SecurityServiceProvider());
            $app['security.firewalls'] = [
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
        }
    }
}
