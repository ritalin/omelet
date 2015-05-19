<?php

namespace Omelet\Domain;

class ObjectDomain extends DomainBase {
    private $fields;
    
    public function __construct(array $fields) {
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
        return new ObjectDomain($values['fields']);
    }
}
