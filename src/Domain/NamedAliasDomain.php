<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class NamedAliasDomain extends DomainBase {
	/**
	 * @var $name
	 */
	private $name;
	/**
	 * @var string
	 */
	private $alias;
    /**
     * @var mixed
     */
    private $default;
    /**
     * @var string[]
     */
    private $optFields;
    
	/**
	 * @var DomainBase
	 */
	private $domain;

	public function __construct(DomainBase $domain, $name, $alias, $default, array $optFields = []) {
        $this->domain = $domain;
        $this->name = $name;
        $this->alias = $alias;
        $this->default = $default;
        $this->optFields = $optFields;
	}
	
	public function getDomain() {
	    return $this->domain;
	}
	
    protected function expandTypesInternal($name, $val) {
        return $this->domain->expandTypes($name, $val);
    }
    
    protected function expandValuesInternal($name, $val) {
        return $this->domain->expandValues($name, $val);
    }
    
    protected function convertResultsInternal($results, AbstractPlatform $platform) {
        return $this->domain->convertResults(
            [ $this->getValues($results) ], $platform
        );
    }
    
    private function getValues(array &$results) {
        $value = $this->getPrimaryValue($results);

        if (count($this->optFields) === 0) return $value;

        return array_merge([ $value ], $this->getOptValues($results));
    }
    
    private function getPrimaryValue(array &$results) {
         if (($this->alias !== null) && isset($results[$this->alias])) {
            return $results[$this->alias];
        }
        else if (isset($results[$this->name])) {
            return $results[$this->name];
        }
        else {
            return $this->default;
        }
    }
    
    private function getOptValues(array &$results) {
        return array_values(array_intersect_key($results, $this->optFields));
    }
    
    public static function __set_state($values) {
        return new self($values['domain'], $values['name'], $values['alias'], $values['default'], $values['optFields']);
    }
}
