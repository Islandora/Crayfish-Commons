<?php

namespace Islandora\Crayfish\Commons;

use Islandora\Chullo\IFedoraApi;
use Symfony\Component\HttpFoundation\Request;

/**
 * Retrieves a Fedora resource using the Apix-Ldp-Resource header. 
 *
 * @package Islandora\Crayfish\Commons
 */
class ApixFedoraResourceRetriever
{

    /**
     * @var \Islandora\Chullo\IFedoraApi
     */
    protected $api;

    /**
     * ApixFedoraResourceRetriever constructor.
     * @param \Islandora\Chullo\IFedoraApi $api
     */
    public function __construct(IFedoraApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getFedoraResource(Request $request)
    {
        // Pass along auth headers if present.
        $headers = [];
        if ($request->headers->has("Authorization")) {
            $headers['Authorization'] = $request->headers->get("Authorization");
        }

        $uri = $request->headers->get("Apix-Ldp-Resource");

        return $this->api->getResource(
            $uri,
            $headers
        );
    }
}
