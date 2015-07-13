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
    
    protected function fetchAll($key, array $options, callable $paramCollecter)
    {
        list($stmt,) = $this->executeQuery($this->queries[$key], $options, $paramCollecter);
        
        return $stmt->fetchAll();
    }

    protected function fetchRow($key, array $options, callable $paramCollecter)
    {
        list($stmt,) = $this->executeQuery($this->queries[$key], $options, $paramCollecter);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    protected function execute($key, array $options, callable $paramCollecter)
    {
        list($stmt, $outParams) = $this->executeQuery($this->queries[$key], $options, $paramCollecter);
        
        if (count($outParams) > 0) {
            return $outParams;
        }
        else {
            return $stmt->rowCount();
        }
    }
    
    protected function executeQuery($query, array $options, callable $paramCollecter)
    {
        $paramPositions = $this->extractParameterPsitions($query);
        
        $outNames = isset($options['returning']) ? $options['returning'] : [];
        
        list ($params, $types) = $paramCollecter(array_diff($paramPositions, $outNames));
        
        list ($query, $params, $types, $outParams) = $this->expandListParameters($query, $paramPositions, $params, $types, array_flip($outNames));

        $stmt = $this->prepare($query, $params, $types);
        
        if (count($outParams) > 0) {
            // bind output parameters
            foreach ($outParams as $i => $p) {
                $stmt->bindParam($i, $outParams[$i]);
            }
            $stmt->setFetchMode(PDO::FETCH_BOUND);
        }
        
        $stmt->execute();
        
        return [ $stmt, &$outParams ];
    }
    
    private function extractParameterPsitions($query)
    {
        return \Doctrine\DBAL\SQLParserUtils::getPlaceholderPositions($query, false);
    }
    
    private function expandListParameters($query, array $paramPositions, array $params, array $types, array $outParamNames)
    {
        $paramIndex = 1;
        $queryOffset = 0;
        $typesOrd    = [];
        $paramsOrd   = [];
        $outParamsOrd= [];
        
        foreach ($paramPositions as $pos => $paramName) {
            $paramLen = strlen($paramName) + 1;
            $value    = $params[$paramName];
            if (in_array($types[$paramName]->getBindingType(), [Connection::PARAM_INT_ARRAY, Connection::PARAM_STR_ARRAY])) {
                $count      = count($value);
                $expandStr  = $count > 0 ? implode(', ', array_fill(0, $count, '?')) : 'NULL';

                foreach ($value as $val) {
                    $paramsOrd[$paramIndex] = $val;
                    $typesOrd[$paramIndex]  = $types[$paramName] - Connection::ARRAY_PARAM_OFFSET;

                    ++$paramIndex;
                }

                $pos         += $queryOffset;
                $queryOffset += (strlen($expandStr) - $paramLen);
                $query        = substr($query, 0, $pos) . $expandStr . substr($query, ($pos + $paramLen));
            }
            else {
                
                if (isset($outParamNames[$paramName])) {
                    $pos         += $queryOffset;
                    $name = $paramName;
                    
                    $outParamsOrd[$paramName] = "";
                }
                else {
                    $pos         += $queryOffset;
                    $queryOffset -= ($paramLen - 1);
                    $name = '?';
                
                    $paramsOrd[$paramIndex]  = $value;
                    $typesOrd[$paramIndex]   = $types[$paramName];
                }
                
                $query = substr($query, 0, $pos) . $name . substr($query, ($pos + $paramLen));
                ++$paramIndex;
            }
        }

        return [ $query, $paramsOrd, $typesOrd, $outParamsOrd ];
    }
    
    private function prepare($query, $params, $types)
    {
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $i => $value) {
            $type = $types[$i];
            
            $stmt->bindValue(
                $i, 
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
