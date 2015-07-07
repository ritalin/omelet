<?php

namespace Omelet\Sequence;

class SequenceNameManager
{
    /**
     * @var string[]
     */
    private $classMap;
    /**
     * @var SequenceNameStrategyInterface[]
     */
    private $cache = [];
    
    public function __construct()
    {
        $this->classMap = [
            'pdo_sqlite' => SqliteSequenceStrategy::class, 
            'default' => DefaultSequenceStrategy::class
        ];
    }
    
    public function findStrategy($driverName)
    {
        if ($driverName !== null) {
            if (($strategy = $this->findStrategyInternal($driverName)) !== false) {
                return $strategy;
            }
        }
        if (($strategy = $this->findStrategyInternal('default')) !== false) {
            return $strategy;
        }
        
        throw new \LogicException('Unknown driver name');
    }
    
    private function findStrategyInternal($driverName)
    {
        if (isset($this->classMap[$driverName])) {
            if (isset($this->cache[$driverName])) {
                return $this->cache[$driverName];
            }
            else {
                $class = $this->classMap[$driverName];
                $strategy = new $class();
                $this->cache[$driverName] = $strategy;
                
                return $strategy;
            }
        }
        
        return false;
    }
}
