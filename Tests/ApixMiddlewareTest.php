<?php

namespace Islandora\Crayfish\Commons\Tests;

use Islandora\Chullo\IFedoraApi;
use Islandora\Crayfish\Commons\ApixMiddleware;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ApixMiddlewareTest
 * @package Islandora\Crayfish\Commons\Tests
 * @coversDefaultClass \Islandora\Crayfish\Commons\ApixMiddleware
 */
class ApixMiddlewareTest extends AbstractCrayfishCommonsTestCase
{

    /**
     * @covers ::before
     * @covers ::getFedoraResource
     */
    public function testReturnsFedoraError()
    {
        $prophecy = $this->prophesize(HttpKernelInterface::class);
        $kernel = $prophecy->reveal();

        // Mock a Fedora response.
        $prophecy = $this->prophesize(ResponseInterface::class);
        $prophecy->getBody()->willReturn();
        $prophecy->getHeaders()->willReturn();
        $prophecy->getStatusCode()->willReturn(401);
        $prophecy->getReasonPhrase()->willReturn("Unauthorized");
        $mock_fedora_response = $prophecy->reveal();

        // Mock a FedoraApi to return the mock response.
        $prophecy = $this->prophesize(IFedoraApi::class);
        $prophecy->getResource(Argument::any(), Argument::any())->willReturn($mock_fedora_response);
        $mock_fedora_api = $prophecy->reveal();

        $middleware = new ApixMiddleware(
            $mock_fedora_api,
            $this->logger
        );

        // Create a Request.
        $request = Request::create(
            "/",
            "GET"
        );
        $request->headers->set('Authorization', 'some_token');
        $request->headers->set('Apix-Ldp-Resource', 'http://localhost:8080/fcrepo/rest/foo');

        $request_event = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        // Test before().
        $middleware->before($request_event);

        $response = $request_event->getResponse();

        $this->assertTrue(
            $response->getStatusCode() == 401,
            "Response code must be Fedora response code"
        );
        $this->assertTrue(
            $response->getContent() == "Unauthorized",
            "Response must return Fedora's reason phrase"
        );
    }

    /**
     * @covers ::before
     * @covers ::getFedoraResource
     */
    public function testReturns400IfNoApixLdpResourceHeader()
    {
        $prophecy = $this->prophesize(HttpKernelInterface::class);
        $kernel = $prophecy->reveal();

        // Mock a FedoraApi.
        $prophecy = $this->prophesize(IFedoraApi::class);
        $mock_fedora_api = $prophecy->reveal();

        // Make the middleware.
        $middleware = new ApixMiddleware(
            $mock_fedora_api,
            $this->logger
        );

        // Create a Request.
        $request = Request::create(
            "/",
            "GET"
        );

        $request_event = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        // Test before().
        $middleware->before($request_event);

        $response = $request_event->getResponse();

          $this->assertTrue(
              $response->getStatusCode() == 400,
              "Response code must be 400 if no ApixLdpResource header is present."
          );
    }
}
