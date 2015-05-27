<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class CustomDomain extends DomainBase {
    /**
     * @var string
     */
    private $type;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var mixed[]
     */
    private $optValues;
    
    /**
     * @param string type
     * @param mixed value
     * @param mixed[] optValues
     */
    public function __construct($type, $value, array $optValues = []) {
        $this->type = $type;
        $this->value = $value;
        $this->optValues = $optValues;
    }
    
    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
    
    /**
     * @return mixed
     */
    protected function getOptValue($name) {
        return isset($this->optValues[$name]) ? $this->optValues[$name]: null;
    }
    
    protected function expandTypesInternal($name, $val) {
        return [$name => Type::getType($this->type)];
    }
    
    protected function expandValuesInternal($name, $val) {
        return [$name => $this->value];
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform) {
    }
}
