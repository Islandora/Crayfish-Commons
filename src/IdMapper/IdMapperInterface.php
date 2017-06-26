<?php

namespace Islandora\Crayfish\Commons\IdMapper;

/**
 * Interface IdMapperInterface
 * @package Islandora\Crayfish\Commons
 */
interface IdMapperInterface
{
    /**
     * @param string $drupal
     * @return mixed string|null
     * @throws \Exception
     */
    public function getMetadataId($drupal);

    /**
     * @param string $drupal
     * @return mixed string|null
     * @throws \Exception
     */
    public function getBinaryId($drupal);

    /**
     * @param string $drupal
     * @param string $fedora
     * @throws \Exception
     */
    public function saveMetadataId($drupal, $fedora);

    /**
     * @param string $drupal
     * @param string $fedora
     * @param string $describedby
     * @throws \Exception
     */
    public function saveBinaryId($drupal, $fedora, $describedby);

    /**
     * @param string $drupal
     * @return boolean
     * @throws \Exception
     */
    public function deleteMetadataId($drupal);

    /**
     * @param string $drupal
     * @return boolean
     * @throws \Exception
     */
    public function deleteBinaryId($drupal);
}
