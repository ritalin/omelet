<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;

abstract class CustomDomain extends DomainBase {
    private $type;
    private $value;
    
    public function __construct($type, $value) {
        $this->type = $type;
        $this->value = $value;
    }
    
    public function getType() {
        return $this->type;
    }
    
    protected function expandTypesInternal($name, $val) {
        return [$name => $this->type];
    }
    
    protected function expandValuesInternal($name, $val) {
        return [$name => $this->value];
    }
}
