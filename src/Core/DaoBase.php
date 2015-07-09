<?php

namespace Omelet\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Connection as ConnectionDriver;
use Omelet\Domain\DomainBase;

class DaoBase
{
    /**
     * @var ConnectionDriver
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

    public function __construct(ConnectionDriver $conn, array $queries, $seqName)
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
    
    protected function fetchAll($key, callable $paramCollecter)
    {
        return $this->executeQuery($this->queries[$key], $paramCollecter)->fetchAll();
    }

    protected function fetchRow($key, callable $paramCollecter)
    {
        return $this->executeQuery($this->queries[$key], $paramCollecter)->fetch(\PDO::FETCH_ASSOC);
    }

    protected function execute($key, callable $paramCollecter)
    {
        return $this->executeQuery($this->queries[$key], $paramCollecter)->rowCount();
    }
    
    protected function executeQuery($query, callable $paramCollecter)
    {
        $paramPositions = $this->extractParameterPsitions($query);
        
        list ($params, $types) = $paramCollecter($paramPositions);
        
        list ($query, $params, $types) = $this->expandListParameters($query, $paramPositions, $params, $types);

        $stmt = $this->prepare($query, $params, $types);
        $stmt->execute();
        
        return $stmt;
    }
    
    private function extractParameterPsitions($query)
    {
        return \Doctrine\DBAL\SQLParserUtils::getPlaceholderPositions($query, false);
    }
    
    private function expandListParameters($query, array $paramPositions, array $params, array $types)
    {
        $queryOffset = 0;
        $typesOrd    = [];
        $paramsOrd   = [];

        foreach ($paramPositions as $pos => $paramName) {
            $paramLen = strlen($paramName) + 1;
            $value    = $params[$paramName];

            if (in_array($types[$paramName]->getBindingType(), [Connection::PARAM_INT_ARRAY, Connection::PARAM_STR_ARRAY])) {
                $count      = count($value);
                $expandStr  = $count > 0 ? implode(', ', array_fill(0, $count, '?')) : 'NULL';

                foreach ($value as $val) {
                    $paramsOrd[] = $val;
                    $typesOrd[]  = $types[$paramName] - Connection::ARRAY_PARAM_OFFSET;
                }

                $pos         += $queryOffset;
                $queryOffset += (strlen($expandStr) - $paramLen);
                $query        = substr($query, 0, $pos) . $expandStr . substr($query, ($pos + $paramLen));
            }
            else {
                $pos         += $queryOffset;
                $queryOffset -= ($paramLen - 1);
                $paramsOrd[]  = $value;
                $typesOrd[]   = $types[$paramName];
                $query        = substr($query, 0, $pos) . '?' . substr($query, ($pos + $paramLen));
            }
        }

        return [ $query, $paramsOrd, $typesOrd ];
    }
    
    private function prepare($query, $params, $types)
    {
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $i => $value) {
            $type = $types[$i];
            
            $stmt->bindValue(
                $i+1, 
                $type->convertToDatabaseValue($value, $this->conn->getDatabasePlatform()), 
                $type->getBindingType()
            );
        }
        
        return $stmt;
    }
    
    protected function convertResults($results, DomainBase $domain)
    {
        return $domain->convertResults($results, $this->conn->getDatabasePlatform());
    }
}
