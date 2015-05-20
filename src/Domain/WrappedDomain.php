<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class WrappedDomain extends DomainBase {
    /**
     * @var string
     */
    private $type;
    
    public function __construct($type) {
        $this->type = $type;
    }
    
    protected function expandTypesInternal($name, $val) {
        return ($val instanceof CustomDomain) ? $val->expandTypes($name, $val, false) : [];
    }

    protected function expandValuesInternal($name, $val) {
        return ($val instanceof CustomDomain) ? $val->expandValues($name, $val, false) : [];
    }
    
    protected function convertResultsInternal($results, AbstractPlatform $platform) {
        if (is_array($results)) {
            $results = current($results);
        }
        
        $class = $this->type;
        
        return new $class($results);
    }
    
    public static function __set_state($values) {
        return new WrappedDomain($values['type']);
    }
}
