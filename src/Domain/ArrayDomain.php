<?php

namespace Omelet\Domain;

class ArrayDomain extends DomainBase {
    private $child;
    
    public function __construct(DomainBase $child) {
        $this->child = $child;
    }
    
    protected function expandTypesInternal($name, $val) {
        if (is_array($val) && count($val) === 0) return [];
        
        return $this->expand($name, $val,
            function ($k, $v) { return $this->child->expandTypes($k, $v); }
        );
    }
    
    protected function expandValuesInternal($name, $val) {
        if (is_array($val) && count($val) === 0) return [];
    
        return $this->expand($name, $val,
            function ($k, $v) { return $this->child->expandValues($k, $v); }
        );
    }
    
    private function expand($name, $val, callable $fn) { 
        return array_reduce(
            array_keys($val),
            function (array &$tmp, $k) use($name, $val, $fn) {    
                $n = $name !== "" ? "{$name}_{$k}": $k;
                
                return $tmp + [$k => $fn($n, $val[$k])]; 
            },
            []
        );
    }
    
    public function childDomain() {
        return $this->child;
    }
}
