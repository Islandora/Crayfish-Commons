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
        $sql = "INSERT INTO Gemini (drupal, fedora) VALUES (:drupal_path, :fedora_path)";
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'drupal_path' => urldecode($drupal_path),
                'fedora_path' => urldecode($fedora_path),
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createBinaryPairs(
        $drupal_binary_path,
        $fedora_binary_path,
        $drupal_rdf_path,
        $fedora_rdf_path
    ) {
        $sql = "INSERT INTO Gemini (drupal, fedora) VALUES (:drupal_binary, :fedora_binary), (:drupal_rdf, :fedora_rdf)";
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'drupal_binary' => urldecode($drupal_binary_path),
                'fedora_binary' => urldecode($fedora_binary_path),
                'drupal_rdf' => urldecode($drupal_rdf_path),
                'fedora_rdf' => urldecode($fedora_rdf_path),
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
