<?php

namespace Islandora\Crayfish\Commons\PathMapper;

/**
 * Interface IdMapperInterface
 * @package Islandora\Crayfish\Commons
 */
interface IdMapperInterface
{
    /**
     * @param string $fedora_id
     * @return mixed string|null
     * @throws \Exception
     */
    public function getDrupalId($fedora_id);

    /**
     * @param string $drupal_id
     * @return mixed string|null
     * @throws \Exception
     */
    public function getFedoraId($drupal_id);

    /**
     * @param string $drupal_id
     * @param string $fedora_id
     * @throws \Exception
     */
    public function createPair($drupal_id, $fedora_id);

    /**
     * @param string $drupal_id
     * @return boolean
     * @throws \Exception
     */
    public function deleteDrupalId($drupal_id);

    /**
     * @param string $fedora_id
     * @return boolean
     * @throws \Exception
     */
    public function deleteFedoraId($fedora_id);
}
