<?php

namespace Islandora\Crayfish\Commons\Tests\EntityMapper;

use Islandora\Crayfish\Commons\EntityMapper\EntityMapper;
use PHPUnit\Framework\TestCase;

class EntityMapperTest extends TestCase
{

    public function testGetFedoraPath()
    {
        $mapper = new EntityMapper();
        $uuid = '9541c0c1-5bee-4973-a9d0-e55c1658bc8';
        $expected = '95/41/c0/c1/9541c0c1-5bee-4973-a9d0-e55c1658bc8';
        $actual = $mapper->getFedoraPath($uuid);
        $this->assertTrue($actual == $expected, "Expected $expected, received $actual");
    }

    public function testGetDrupalUuid()
    {
        $mapper = new EntityMapper();
        $path = '95/41/c0/c1/9541c0c1-5bee-4973-a9d0-e55c1658bc8';
        $expected = '9541c0c1-5bee-4973-a9d0-e55c1658bc8';
        $actual = $mapper->getDrupalUuid($path);
        $this->assertTrue($actual == $expected, "Expected $expected, received $actual");
    }
}
