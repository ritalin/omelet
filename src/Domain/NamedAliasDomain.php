<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class NamedAliasDomain extends DomainBase
{
    /**
     * @var string
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

    public function __construct(DomainBase $domain, $name, $alias, $default, array $optFields = [])
    {
        $this->domain = $domain;
        $this->name = $name;
        $this->alias = $alias;
        $this->default = $default;
        $this->optFields = $optFields;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getOptFields()
    {
        return array_keys($this->optFields);
    }

    public function getDomain()
    {
        return $this->domain;
    }

    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        $n = $sensor->convert($name, $this->name);
        
        if ($val instanceof DomainBase) {
            return  $val->expandTypes($availableParams, $n, $val, $sensor); 
        }
        else {
            return $this->domain->expandTypes($availableParams, $n, $val, $sensor);
        }
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        $n = $sensor->convert($name, $this->name);
        
        if ($val instanceof DomainBase) {
            return  $val->expandValues($availableParams, $n, $val, $sensor); 
        }
        else {
            return $this->domain->expandValues($availableParams, $n, $val, $sensor);
        }
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform)
    {
        return $this->domain->convertResults(
            [ $this->getValues($results) ], $platform
        );
    }

    private function getValues(array &$results)
    {
        $value = $this->getPrimaryValue($results);

        if (count($this->optFields) === 0) return $value;

        return array_values(array_merge([ $value ], $this->getOptValues($results)));
    }

    private function getPrimaryValue(array &$results)
    {
        $value = null;
        if ((! empty($this->alias)) && isset($results[$this->alias])) {
            return $results[$this->alias];
        }
        elseif (isset($results[$this->name])) {
            return $results[$this->name];
        }
        else {
            return $this->default;
        }
    }

    private function getOptValues(array &$results)
    {
        return array_reduce(
            array_keys($this->optFields),
            function (array &$tmp, $name) use ($results) {
                return $tmp + [$name => isset($results[$name]) ? $results[$name] : null];
            },
            []
        );
    }

    public static function __set_state($values)
    {
        return new self($values['domain'], $values['name'], $values['alias'], $values['default'], $values['optFields']);
    }
}
