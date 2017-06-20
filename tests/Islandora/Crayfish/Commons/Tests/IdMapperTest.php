<?php

namespace Islandora\Crayfish\Commons\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Islandora\Crayfish\Commons\IdMapper\IdMapper;
use Prophecy\Argument;

class IdMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFedoraIdReturnsResultOnSuccess()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetch()->willReturn(['fedora' => 'foo']);
        $statement = $statement->reveal();

        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $db->reveal();

        $path_mapper = new IdMapper($db);

        $result = $path_mapper->getFedoraId("bar");
        $this->assertTrue(
            $result == 'foo',
            "Expected 'foo', received $result"
        );
    }

    public function testGetFedoraIdReturnsNullIfNotFound()
    {
        $prophecy = $this->prophesize(Statement::class);
        $prophecy->fetch()->willReturn([]);
        $statement = $prophecy->reveal();

        $prophecy = $this->prophesize(Connection::class);
        $prophecy->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $prophecy->reveal();

        $path_mapper = new IdMapper($db);

        $result = $path_mapper->getFedoraId("foo");
        $this->assertTrue(
            $result === null,
            "Expected null, received $result"
        );
    }

    public function testGetDrupalIdReturnsResultOnSuccess()
    {
        $statement = $this->prophesize(Statement::class);
        $statement->fetch()->willReturn(['drupal' => 'foo']);
        $statement = $statement->reveal();

        $db = $this->prophesize(Connection::class);
        $db->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $db->reveal();

        $path_mapper = new IdMapper($db);

        $result = $path_mapper->getDrupalId("bar");
        $this->assertTrue(
            $result == 'foo',
            "Expected 'foo', received $result"
        );
    }

    public function testGetDrupalIdReturnsNullIfNotFound()
    {
        $prophecy = $this->prophesize(Statement::class);
        $prophecy->fetch()->willReturn([]);
        $statement = $prophecy->reveal();

        $prophecy = $this->prophesize(Connection::class);
        $prophecy->executeQuery(Argument::Any(), Argument::Any())
            ->willReturn($statement);
        $db = $prophecy->reveal();

        $path_mapper = new IdMapper($db);

        $result = $path_mapper->getDrupalId("foo");
        $this->assertTrue(
            $result === null,
            "Expected null, received $result"
        );
    }
}
