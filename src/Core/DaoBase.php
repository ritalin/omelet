<?php

namespace Omelet\Core;

use Doctrine\DBAL\Driver\Connection;
use Omelet\Domain\DomainBase;

class DaoBase
{
    /**
     * @var Connection
     */
    private $conn;
    /**
     * @var string[]
     */
    private $queries;
    /**
     * @var string
     */
    private $seqName;

    public function __construct(Connection $conn, array $queries, $seqName)
    {
        $this->conn = $conn;
        $this->queries = $queries;
        $this->seqName = $seqName;
    }

    /**
     * @return string
     */
    public function sequenceName()
    {
        return $this->seqName;
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->conn->lastInsertId($this->seqName);
    }

    protected function fetchAll($key, array $params, array $types)
    {
        return $this->conn->fetchAll($this->queries[$key], $params, $types);
    }

    protected function fetchRow($key, array $params, array $types)
    {
        return $this->conn->fetchAssoc($this->queries[$key], $params, $types);
    }

    protected function execute($key, array $params, array $types)
    {
        return $this->conn->executeUpdate($this->queries[$key], $params, $types);
    }

    protected function convertResults($results, DomainBase $domain)
    {
        return $domain->convertResults($results, $this->conn->getDatabasePlatform());
    }
}
