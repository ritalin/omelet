<?php

namespace Omelet\Core;

use Doctrine\DBAL\Driver\Connection;

class DaoBase {
    private $conn;
    private $queries;
    
    public function __construct(Connection $conn, array $queries) {
        $this->conn = $conn;
        $this->queries = $queries;
    }
    
    protected function fetchAll($key, array $params, array $types) {
        return $this->conn->fetchAll($this->queries[$key], $params, $types);
    }
    
    protected function execute($key, array $params, array $types) {
        return $this->conn->executeUpdate($this->queries[$key], $params, $types);
    }
}
