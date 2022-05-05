<?php

namespace Islandora\Crayfish\Commons\Tests\Syn;

use Islandora\Crayfish\Commons\Syn\SettingsParser;
use Islandora\Crayfish\Commons\Tests\AbstractCrayfishCommonsTestCase;

class SettingsParserTokenTest extends AbstractCrayfishCommonsTestCase
{

    public function testInvalidVersion()
    {
        $testXml =  <<<STRING
<config version='2'>
  <token>
    c00lpazzward
  </token>
</config>
STRING;
        $parser = new SettingsParser($testXml, $this->logger);
        $tokens = $parser->getStaticTokens();
        $this->assertEquals(0, count($tokens));
    }

    public function testTokenNoParams()
    {
        $testXml =  <<<STRING
<config version='1'>
  <token>
    c00lpazzward
  </token>
</config>
STRING;
        $parser = new SettingsParser($testXml, $this->logger);
        $tokens = $parser->getStaticTokens();
        $this->assertEquals(1, count($tokens));
        $this->assertTrue(isset($tokens['c00lpazzward']));
        $token = $tokens['c00lpazzward'];
        $this->assertEquals('c00lpazzward', $token['token']);
        $this->assertEquals('islandoraAdmin', $token['user']);
        $this->assertEquals(0, count($token['roles']));
    }

    public function testTokenUser()
    {
        $testXml =  <<<STRING
<config version='1'>
  <token user="dennis">
    c00lpazzward
  </token>
</config>
STRING;
        $parser = new SettingsParser($testXml, $this->logger);
        $tokens = $parser->getStaticTokens();
        $this->assertEquals(1, count($tokens));
        $this->assertTrue(isset($tokens['c00lpazzward']));
        $token = $tokens['c00lpazzward'];
        $this->assertEquals('dennis', $token['user']);
    }


    public function testTokenRole()
    {
        $testXml =  <<<STRING
<config version='1'>
  <token roles="dennis,dee,charlie,mac">
    c00lpazzward
  </token>
</config>
STRING;
        $parser = new SettingsParser($testXml, $this->logger);
        $tokens = $parser->getStaticTokens();
        $this->assertEquals(1, count($tokens));
        $this->assertTrue(isset($tokens['c00lpazzward']));
        $token = $tokens['c00lpazzward'];
        $this->assertEquals(4, count($token['roles']));
        $this->assertTrue(in_array('dennis', $token['roles']));
        $this->assertTrue(in_array('dee', $token['roles']));
        $this->assertTrue(in_array('charlie', $token['roles']));
        $this->assertTrue(in_array('mac', $token['roles']));
    }
}
