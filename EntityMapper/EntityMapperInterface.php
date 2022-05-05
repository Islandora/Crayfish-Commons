<?php

namespace Islandora\Crayfish\Commons\EntityMapper;

interface EntityMapperInterface
{
    /**
     * Gets a fedora path given a uuid.
     *
     * @param string $uuid
     * @return string
     */
    public function getFedoraPath($uuid);

    /**
     * Gets a drupal uuid from a fedora path.
     *
     * @param string $fedora_path
     * @return string
     */
    public function getDrupalUuid($fedora_path);
}
