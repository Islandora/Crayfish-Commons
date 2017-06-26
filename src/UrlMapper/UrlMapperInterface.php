<?php

namespace Islandora\Crayfish\Commons\UrlMapper;

/**
 * Interface UrlMapperInterface
 * @package Islandora\Crayfish\Commons
 */
interface UrlMapperInterface
{
    /**
     * @param string $uuid
     * @return mixed array|null
     * @throws \Exception
     */
    public function getUrls($uuid);

    /**
     * @param string $uuid
     * @param string $drupal_rdf
     * @param string $fedora_rdf
     * @param string $drupal_nonrdf
     * @param string $fedora_nonrdf
     * @throws \Exception
     */
    public function saveUrls(
        $uuid,
        $drupal_rdf,
        $fedora_rdf,
        $drupal_nonrdf = null,
        $fedora_nonrdf = null
    );

    /**
     * @param string $uuid
     * @throws \Exception
     */
    public function deleteUrls($uuid);
}
