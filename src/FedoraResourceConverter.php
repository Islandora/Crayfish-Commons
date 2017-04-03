<?php

namespace Islandora\Crayfish\Commons;

use Islandora\Chullo\IFedoraApi;
use Symfony\Component\HttpFoundation\Request;

/**
 * Converts a path provided as a route argument into a HTTP response from
 * Fedora.
 *
 * @package Islandora\Crayfish\Commons
 */
class FedoraResourceConverter
{

    /**
     * @var \Islandora\Chullo\IFedoraApi
     */
    protected $api;

    /**
     * FedoraResourceConverter constructor.
     * @param \Islandora\Chullo\IFedoraApi $api
     */
    public function __construct(IFedoraApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param string $path
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function convert($path, Request $request)
    {
        // Pass along auth headers if present.
        $headers = [];
        if ($request->headers->has("Authorization")) {
            $headers['Authorization'] = $request->headers->get("Authorization");
        }

        return $this->api->getResource(
            $path,
            $headers
        );
    }
}
