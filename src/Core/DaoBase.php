<?php

namespace Omelet\Core;

use Doctrine\DBAL\Driver\Connection;
use Omelet\Domain\DomainBase;

class DaoBase
{
    private $conn;
    private $queries;

    public function __construct(Connection $conn, array $queries)
    {
        $this->conn = $conn;
        $this->queries = $queries;
    }
    
    /**
     * @param string sequenceName
     *
     * @return string
     */
    protected function lastInsertIdInternal($sequenceName)
    {
        return $this->conn->lastInsertId($sequenceName);
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
