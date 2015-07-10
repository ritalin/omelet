<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class ObjectDomain extends DomainBase {
    /**
     * @var string
     */
    private $type;
    /** 
     * @var NamedDomain[]
     */
    private $fields;
    
    public function __construct($type, array $fields) {
        $this->type = $type;
        $this->fields = $fields;
    }
    
    public function getChildren() {
        return $this->fields;
    }
    
    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor) {
        return $this->expand(
            $name, $val, $sensor, 
            function ($field, $k, $v) use ($availableParams, $sensor) {
                return $field->expandTypes($availableParams, $k, $v, $sensor, false);
            }
        );
    }
    
    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor) {
        return $this->expand(
            $name, $val, $sensor,
            function ($field, $k, $v) use ($availableParams, $sensor) {
                return $field->expandValues($availableParams, $k, $v, $sensor, false);
            }
        );
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform) {
        if ($results === false) {
            return null;
        }
        
        if (is_int(key($results))) {
            $results = current($results);
        }
        
        $class = $this->type;
        
        $obj = new $class();

        foreach ($this->fields as $name => $domain) {
            $obj->{$name} = $domain->convertResults($results, $platform);
        }
        
        return $obj;
    }
    
    private function expand($name, $val, CaseSensor $sensor, callable $fn) {
        return array_reduce(
            array_keys($this->fields),
            function (array &$tmp, $k) use($name, $val, $sensor, $fn) {
                $n = $name !== "" ? $sensor->convert($name, $k) : $k;
                return $tmp + [$k => $fn($this->fields[$k], $name, $val->{$k})];
            },
            []
        );
    }
    
    public static function __set_state($values) {
        return new ObjectDomain($values['type'], $values['fields']);
    }
}
