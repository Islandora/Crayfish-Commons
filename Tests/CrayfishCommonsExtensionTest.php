<?php

namespace Islandora\Crayfish\Commons\Tests;

use Islandora\Crayfish\Commons\ApixMiddleware;
use Islandora\Crayfish\Commons\CmdExecuteService;
use Islandora\Crayfish\Commons\DependencyInjection\CrayfishCommonsExtension;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CrayfishCommonsExtensionTest extends AbstractCrayfishCommonsTestCase
{
    public function testLoadsBundleServices(): void
    {
        $container = new ContainerBuilder();

        (new CrayfishCommonsExtension())->load([], $container);
        $container->register(LoggerInterface::class, NullLogger::class);
        $container->compile();

        $this->assertInstanceOf(CmdExecuteService::class, $container->get(CmdExecuteService::class));
        $this->assertInstanceOf(ApixMiddleware::class, $container->get(ApixMiddleware::class));
    }

    public function testCanDisableApixMiddleware(): void
    {
        $container = new ContainerBuilder();

        (new CrayfishCommonsExtension())->load([
            ['apix_middleware_enabled' => false],
        ], $container);
        $container->register(LoggerInterface::class, NullLogger::class);
        $container->compile();

        $this->assertFalse($container->has(ApixMiddleware::class));
    }
}
