<?php

namespace Islandora\Crayfish\Commons\Tests;

use Islandora\Crayfish\Commons\CmdExecuteService;
use function rewind;

/**
 * Class CmdExecuteServiceTest
 * @package Islandora\Crayfish\Commons\Tests
 * @coversDefaultClass \Islandora\Crayfish\Commons\CmdExecuteService
 */
class CmdExecuteServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsInput()
    {
        $cmd = new CmdExecuteService();
        $data = fopen("php://temp", 'w+');
        $output = $cmd->execute('echo foo', $data);
        $this->expectOutputString("foo\n");
        $output();
    }

    public function testReturnsData()
    {
        $cmd = new CmdExecuteService();
        $data = fopen("php://temp", 'w+');
        fwrite($data, "foo\n");
        rewind($data);
        $output = $cmd->execute('cat', $data);
        $this->expectOutputString("foo\n");
        $output();
    }
}
