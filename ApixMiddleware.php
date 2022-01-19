<?php

namespace Islandora\Crayfish\Commons;

use Islandora\Chullo\IFedoraApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Retrieves a Fedora resource using the Apix-Ldp-Resource header.
 *
 * @package Islandora\Crayfish\Commons
 */
class ApixMiddleware implements EventSubscriberInterface
{

    /**
     * @var \Islandora\Chullo\IFedoraApi
     */
    protected $api;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * ApixFedoraResourceRetriever constructor.
     * @param \Islandora\Chullo\IFedoraApi $api
     * @param \Psr\Log\LoggerInterface $log
     */
    public function __construct(
        IFedoraApi $api,
        LoggerInterface $log
    ) {
        $this->api = $api;
        $this->log = $log;
    }

    /**
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function before(RequestEvent $event)
    {

        $request = $event->getRequest();

        // Short circuit if this is an OPTIONS or HEAD request.
        if (in_array(
            strtoupper($request->getMethod()),
            ['OPTIONS', 'HEAD']
        )) {
            return;
        }

        // Short circuit if there's no Apix-Ldp-Resource header.
        if (!$request->headers->has("Apix-Ldp-Resource")) {
            $this->log->debug("No Apix-Ldp-Resource header present, no fedora_resource set");
            $request->attributes->set('fedora_resource', false);
            return;
        }

        // Get the resource.
        $fedora_resource = $this->getFedoraResource($request);

        // Short circuit if the Fedora response is not 200.
        $status = $fedora_resource->getStatusCode();
        if ($status != 200) {
            $this->log->debug("Fedora Resource: ", [
              'body' => $fedora_resource->getBody(),
              'status' => $fedora_resource->getStatusCode(),
              'headers' => $fedora_resource->getHeaders()
            ]);
            $event->setResponse(new Response(
                $fedora_resource->getReasonPhrase(),
                $status
            ));
            return;
        }

        // Set the Fedora resource on the request.
        $request->attributes->set('fedora_resource', $fedora_resource);
    }

    protected function getFedoraResource(Request $request)
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['before', 0],
            ],
        ];
    }
}
