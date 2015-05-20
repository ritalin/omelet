<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class DomainBase {
    protected abstract function expandTypesInternal($name, $val);
    protected abstract function expandValuesInternal($name, $val);
    
    public function expandTypes($name, $val, $root = true) {
        $types = $this->expandTypesInternal($name, $val);
        
        return $root ? $this->flatten($types) : $types;
    }
    
    public function expandValues($name, $val, $root = true) {
        $values = $this->expandValuesInternal($name, $val);
        
        return $root ? $this->flatten($values) : $values;
    }
    
    private function flatten(array $a) {
        return array_reduce(
            array_keys($a),
            function (array &$tmp, $k) use($a) {
                return is_array($a[$k]) ? $tmp + $this->flatten($a[$k]) : $tmp + [$k => $a[$k]];
            },
            []
        );
    }
    
    public function convertResults($results, AbstractPlatform $platform) {
        return null;
    }
}
