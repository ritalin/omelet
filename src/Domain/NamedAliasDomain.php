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
	 * @var DomainBase
	 */
	private $domain;

	public function __construct(DomainBase $domain, $name, $alias = null) {
        $this->domain = $domain;
        $this->name = $name;
        $this->alias = $alias;
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
        if (($this->alias !== null) && isset($results[$this->alias])) {
            return $this->domain->convertResults($results[$this->alias], $platform);
        }
        else if (isset($results[$this->name])) {
            return $this->domain->convertResults($results[$this->name], $platform);
        }
        else {
            return null;
        }
    }
    
    public static function __set_state($values) {
        return new self($values['domain'], $values['name'], $values['alias']);
    }
}
