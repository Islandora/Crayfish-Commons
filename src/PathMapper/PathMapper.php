<?php

namespace Islandora\Crayfish\Commons\PathMapper;

use Doctrine\DBAL\Connection;

/**
 * Class PathMapper
 * @package Islandora\Crayfish\Commons
 */
class PathMapper implements PathMapperInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * PathMapper constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getFedoraPath($drupal_path)
    {
        $sql = "SELECT fedora FROM Gemini WHERE drupal = :path";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['path' => urldecode($drupal_path)]
        );
        $result = $stmt->fetch();

        if (isset($result['fedora'])) {
            return $result['fedora'];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getDrupalPath($fedora_path)
    {
        $sql = "SELECT drupal FROM Gemini WHERE fedora = :path";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['path' => urldecode($fedora_path)]
        );
        $result = $stmt->fetch();

        if (isset($result['drupal'])) {
            return $result['drupal'];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function createPair($drupal_path, $fedora_path)
    {
        $this->connection->insert(
            'Gemini',
            [
                'drupal_path' => urldecode($drupal_path),
                'fedora_path' => urldecode($fedora_path),
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFromDrupalPath($drupal_path)
    {
        return $this->connection->delete(
            'Gemini',
            ['drupal' => urldecode($drupal_path)]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFromFedoraPath($fedora_path)
    {
        return $this->connection->delete(
            'Gemini',
            ['fedora' => urldecode($fedora_path)]
        );
    }
}
