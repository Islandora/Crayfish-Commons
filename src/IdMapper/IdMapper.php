<?php

namespace Islandora\Crayfish\Commons\PathMapper;

use Doctrine\DBAL\Connection;

/**
 * Class PathMapper
 * @package Islandora\Crayfish\Commons
 */
class IdMapper implements IdMapperInterface
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * IdMapper constructor.
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getFedoraId($drupal_id)
    {
        $sql = "SELECT fedora FROM Gemini WHERE drupal = :id";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['id' => $drupal_id]
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
    public function getDrupalId($fedora_id)
    {
        $sql = "SELECT drupal FROM Gemini WHERE fedora = :id";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['id' => $fedora_id]
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
    public function createPair($drupal_id, $fedora_id)
    {
        $this->connection->insert(
            'Gemini',
            ['drupal' => $drupal_id, 'fedora' => $fedora_id]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFromDrupalPath($drupal_id)
    {
        return $this->connection->delete(
            'Gemini',
            ['drupal' => $drupal_id]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFromFedoraPath($fedora_id)
    {
        return $this->connection->delete(
            'Gemini',
            ['fedora' => $fedora_id]
        );
    }
}
