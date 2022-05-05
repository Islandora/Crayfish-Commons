<?php

namespace Islandora\Crayfish\Commons\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AbstractCrayfishCommonsTestCase extends TestCase
{

    use ProphecyTrait;

    protected $logger;

    public function setUp(): void
    {
        $this->logger = new Logger('crayfish-commons-tests');
        $this->logger->pushHandler(new NullHandler());
    }
}
