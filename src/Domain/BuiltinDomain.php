<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;

class BuiltinDomain extends DomainBase {
    private $type;
    
    public function __construct($type) {
        $this->type = $type;
    }
    
    public function getType() {
        return $this->type;
    }
    
    protected function expandTypesInternal($name, $val) {
        return [$name => Type::getType($this->type)];
    }
    
    protected function expandValuesInternal($name, $val) {
        return [$name => $val];
    }
    
    public static function __set_state($values) {
        return new BuiltinDomain($values['type']);
    }
}
