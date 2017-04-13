<?php

namespace Islandora\Crayfish\Commons\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Islandora\Crayfish\Commons\PathMapper\PathMapper;
use Prophecy\Argument;

class PathMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFedoraPathReturnsResultOnSuccess()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetch()->willReturn(['fedora' => 'foo']);
        $statement = $statement->reveal();

        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $db->reveal();

        $path_mapper = new PathMapper($db);

        $result = $path_mapper->getFedoraPath("bar");
        $this->assertTrue(
            $result == 'foo',
            "Expected 'foo', received $result"
        );
    }

    public function testGetFedoraPathReturnsNullIfNotFound()
    {
        $prophecy = $this->prophesize(Statement::class);
        $prophecy->fetch()->willReturn([]);
        $statement = $prophecy->reveal();

        $prophecy = $this->prophesize(Connection::class);
        $prophecy->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $prophecy->reveal();

        $path_mapper = new PathMapper($db);

        $result = $path_mapper->getFedoraPath("foo");
        $this->assertTrue(
            $result === null,
            "Expected null, received $result"
        );
    }

    public function testGetDrupalPathReturnsResultOnSuccess()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetch()->willReturn(['drupal' => 'foo']);
        $statement = $statement->reveal();

        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $db->reveal();

        $path_mapper = new PathMapper($db);

        $result = $path_mapper->getDrupalPath("bar");
        $this->assertTrue(
            $result == 'foo',
            "Expected 'foo', received $result"
        );
    }

    public function testGetDrupalPathReturnsNullIfNotFound()
    {
        $prophecy = $this->prophesize(Statement::class);
        $prophecy->fetch()->willReturn([]);
        $statement = $prophecy->reveal();

        $prophecy = $this->prophesize(Connection::class);
        $prophecy->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $prophecy->reveal();

        $path_mapper = new PathMapper($db);

        $result = $path_mapper->getDrupalPath("foo");
        $this->assertTrue(
            $result === null,
            "Expected null, received $result"
        );
    }
}
