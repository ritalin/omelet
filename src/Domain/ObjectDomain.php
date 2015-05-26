<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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
    
    protected function expandTypesInternal($name, $val) {
        return $this->expand(
            $name, $val, 
            function ($field, $k, $v) {
                return $field->expandTypes($k, $v);
            }
        );
    }
    
    protected function expandValuesInternal($name, $val) {
        return $this->expand(
            $name, $val, 
            function ($field, $k, $v) {
                return $field->expandValues($k, $v);
            }
        );
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform) {
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
    
    private function expand($name, $val, callable $fn) {
        return array_reduce(
            array_keys($this->fields),
            function (array &$tmp, $k) use($name, $val, $fn) {
                $n = $name !== "" ? "{$name}_{$k}" : $k;
                return $tmp + [$k => $fn($this->fields[$k], $n, $val->{$k})];
            },
            []
        );
    }
    
    public static function __set_state($values) {
        return new ObjectDomain($values['type'], $values['fields']);
    }
}
