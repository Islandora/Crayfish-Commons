<?php

namespace Islandora\Crayfish\Commons\IdMapper;

use Doctrine\DBAL\Connection;

/**
 * Class IdMapper
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
    public function getMetadataId($drupal)
    {
        $sql = "SELECT fedora FROM Metadata WHERE drupal = :drupal";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['drupal' => $drupal]
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
    public function getBinaryId($drupal)
    {
        $sql = "SELECT fedora FROM Binary WHERE drupal = :drupal";
        $stmt = $this->connection->executeQuery(
            $sql,
            ['drupal' => $drupal]
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
    public function saveMetadataId($drupal, $fedora)
    {
        $sql = "UPDATE Metadata SET fedora = :fedora WHERE drupal = :drupal";

        $count = $this->connection->executeUpdate(
            $sql,
            ['drupal' => $drupal, 'fedora' => $fedora]
        );

        if (!$count) {
            $count = $this->connection->insert(
                'Metadata',
                ['drupal' => $drupal, 'fedora' => $fedora]
            );
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     */
    public function saveBinaryId($drupal, $fedora, $describedby)
    {
        $sql = "UPDATE Binary SET fedora = :fedora, describedby = :describedby WHERE drupal = :drupal";

        $count = $this->connection->executeUpdate(
            $sql,
            ['drupal' => $drupal, 'fedora' => $fedora, 'describedby' => $describedby]
        );

        if (!$count) {
            $count = $this->connection->insert(
                'Binary',
                ['drupal' => $drupal, 'fedora' => $fedora, 'describedby' => $describedby]
            );
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMetadataId($drupal)
    {
        return $this->connection->delete(
            'Metadata',
            ['drupal' => $drupal]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deleteBinaryId($describedby)
    {
        return $this->connection->delete(
            'Binary',
            ['describedby' => $describedby]
        );
    }
}
