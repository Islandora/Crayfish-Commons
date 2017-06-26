<?php

namespace Islandora\Crayfish\Commons\UrlMapper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Class UrlMapper
 * @package Islandora\Crayfish\Commons
 */
class UrlMapper implements UrlMapperInterface
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
    public function getUrls($uuid)
    {
        $sql = 'SELECT Rdf.drupal AS "drupal_rdf", Rdf.fedora AS "fedora_rdf", NonRdf.drupal as "drupal_nonrdf", NonRdf.fedora AS "fedora_nonrdf" ' .
               'FROM Rdf ' .
               'LEFT JOIN NonRdf ON Rdf.uuid=NonRdf.uuid ' .
               'WHERE Rdf.uuid = :uuid';
        $results = $this->connection->fetchAssoc(
            $sql,
            ['uuid' => $uuid]
        );

        // Filter out non-null results.
        return array_filter($results, function($elem) {
            return $elem;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function saveUrls(
        $uuid,
        $drupal_rdf,
        $fedora_rdf,
        $drupal_nonrdf = null,
        $fedora_nonrdf = null
    ) {
        $this->connection->transactional(function() use ($uuid, $drupal_rdf, $fedora_rdf, $drupal_nonrdf, $fedora_nonrdf) {
            // Save rdf.
            // Try to insert first, and if the record already exists, upate it.
            try {
                $this->connection->insert(
                    'Rdf',
                    ['uuid' => $uuid, 'drupal' => $drupal_rdf, 'fedora' => $fedora_rdf]
                );
            }
            catch (UniqueConstraintViolationException $e) {
                $sql = "UPDATE Rdf SET fedora = :fedora, drupal = :drupal WHERE uuid = :uuid";
                $this->connection->executeUpdate(
                    $sql,
                    ['uuid' => $uuid, 'drupal' => $drupal_rdf, 'fedora' => $fedora_rdf]
                );
            }

            // Exit if there's no nonrdfs to save.
            if (empty($drupal_nonrdf) || empty($fedora_nonrdf)) {
                return $count;
            }

            try {
                $this->connection->insert(
                    'NonRdf',
                    ['uuid' => $uuid, 'drupal' => $drupal_nonrdf, 'fedora' => $fedora_nonrdf]
                );
            }
            catch (UniqueConstraintViolationException $e) {
                $sql = "UPDATE NonRdf SET drupal = :drupal, fedora = :fedora WHERE uuid = :uuid";
                $this->connection->executeUpdate(
                    $sql,
                    ['uuid' => $uuid, 'drupal' => $drupal_nonrdf, 'fedora' => $fedora_nonrdf]
                );
            }

            return true;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUrls($uuid)
    {
        $this->connection->transactional(function() use ($uuid) {
            $this->connection->delete(
                'NonRdf',
                ['uuid' => $uuid]
            );
            $this->connection->delete(
                'Rdf',
                ['uuid' => $uuid]
            );
        });
    }
}
