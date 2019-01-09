<?php

namespace Islandora\Crayfish\Commons\Client\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Islandora\Crayfish\Commons\Client\GeminiClient;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Class GeminiClientTest
 * @package Islandora\Crayfish\Commons\Client\Tests
 * @coversDefaultClass Islandora\Crayfish\Commons\Client\GeminiClient
 */
class GeminiClientTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->logger = new Logger('milliner');
        $this->logger->pushHandler(new NullHandler());
    }

    /**
     * @covers ::getUrls
     * @covers ::__construct
     */
    public function testGetUrls()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn('{"drupal" : "foo", "fedora": "bar"}');
        $response = $response->reveal();

        $client = $this->prophesize(Client::class);
        $client->get(Argument::any(), Argument::any())->willReturn($response);
        $client = $client->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $out = $gemini->getUrls("abc123");
        $this->assertTrue(
            $out['drupal'] == 'foo',
            "Gemini client must return response unaltered.  Expected 'foo' but received {$out['drupal']}"
        );
        $this->assertTrue(
            $out['fedora'] == 'bar',
            "Gemini client must return response unaltered.  Expected 'bar' but received {$out['fedora']}"
        );

        $out = $gemini->getUrls("abc123", "some_token");
        $this->assertTrue(
            $out['drupal'] == 'foo',
            "Gemini client must return response unaltered.  Expected 'foo' but received {$out['drupal']}"
        );
        $this->assertTrue(
            $out['fedora'] == 'bar',
            "Gemini client must return response unaltered.  Expected 'bar' but received {$out['fedora']}"
        );
    }

    /**
     * @covers ::getUrls
     */
    public function testGetUrlsReturnsEmptyArrayWhenNotFound()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(404);
        $response = $response->reveal();

        $client = $this->prophesize(Client::class);
        $client->get(Argument::any(), Argument::any())->willThrow(
            new RequestException("Not Found", $request, $response)
        );
        $client = $client->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $this->assertTrue(
            empty($gemini->getUrls("abc123")),
            "Gemini client must return empty array if not found"
        );
        $this->assertTrue(
            empty($gemini->getUrls("abc123", "some_token")),
            "Gemini client must return empty array if not found"
        );
    }

    /**
     * @covers ::mintFedoraUrl
     */
    public function testMintFedoraUrl()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn("http://foo.com/bar");
        $response = $response->reveal();

        $client = $this->prophesize(Client::class);
        $client->post(Argument::any(), Argument::any())->willReturn($response);
        $client = $client->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $url = $gemini->mintFedoraUrl("abc123");
        $this->assertTrue(
            $url == "http://foo.com/bar",
            "Gemini client must return response unaltered.  Expected 'http://foo.com/bar' but received $url"
        );

        $url = $gemini->mintFedoraUrl("abc123", "some_token");
        $this->assertTrue(
            $url == "http://foo.com/bar",
            "Gemini client must return response unaltered.  Expected 'http://foo.com/bar' but received $url"
        );
    }

    /**
     * @covers ::saveUrls
     */
    public function testSaveUrls()
    {
        $client = $this->prophesize(Client::class)->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $out = $gemini->saveUrls("abc123", "foo", "bar");
        $this->assertTrue(
            $out,
            "Gemini client must return true on successful saveUrls().  Received $out"
        );

        $out = $gemini->saveUrls("abc123", "foo", "bar", "some_token");
        $this->assertTrue(
            $out,
            "Gemini client must return true on successful saveUrls().  Received $out"
        );
    }

    /**
     * @covers ::deleteUrls
     */
    public function testDeleteUrls()
    {
        $client = $this->prophesize(Client::class)->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $out = $gemini->deleteUrls("abc123");
        $this->assertTrue(
            $out,
            "Gemini client must return true on successful deleteUrls().  Received $out"
        );

        $out = $gemini->deleteUrls("abc123", "some_token");
        $this->assertTrue(
            $out,
            "Gemini client must return true on successful deleteUrls().  Received $out"
        );
    }

  /**
   * @covers ::findByUri
   */
    public function testGetByUriFailed()
    {
        $prophesize = $this->prophesize(Client::class);

        $request = new Request('GET', '/by_uri');
        $prophesize->get(Argument::any(), Argument::any())
        ->willThrow(new ClientException('Uri not found', $request));
        $client = $prophesize->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $out = $gemini->findByUri('blah');
        $this->assertNull(
            $out,
            "Gemini client must return null when 404 is returned."
        );

        $out = $gemini->findByUri('blah', 'some_token');
        $this->assertNull(
            $out,
            "Gemini client must return null when 404 is returned."
        );
    }

  /**
   * @covers ::findByUri
   */
    public function testGetByUriOk()
    {
        $prophesize = $this->prophesize(Client::class);

        $prophesize->get(Argument::any(), Argument::any())
        ->willReturn(new Response(
            200,
            ['Location' => 'some-uri']
        ));
        $client = $prophesize->reveal();

        $gemini = new GeminiClient(
            $client,
            $this->logger
        );

        $out = $gemini->findByUri('blah');
        $this->assertNotNull(
            $out,
            "Gemini client must return null when 404 is returned."
        );

        $out = $gemini->findByUri('blah', 'some_token');
        $this->assertNotNull(
            $out,
            "Gemini client must return null when 404 is returned."
        );
    }

  /**
   * @covers ::create
   */
    public function testCreate()
    {
        $base_url = 'http://localhost:1234/example';
        $client = GeminiClient::create($base_url, $this->logger);
        $this->assertTrue($client instanceof GeminiClient);
    }
}
