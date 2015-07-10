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
     * @var string[]
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

    public function __construct(DomainBase $domain, $name, array $alias, $default, array $optFields = [])
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
    
    /**
     * @return string[]
     */
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
    
    private function resolveName(array $availables, $baseName, array $subNames, CaseSensor $sensor)
    {
        foreach ($subNames as $name) {
            $n = $sensor->convert($baseName, $name);
            if (isset($availables[$n])) {
                return $n;
            }
        }
        
        return false;
    }
    
    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        if (($n = $this->resolveName(array_flip($availableParams), $name, array_merge([$this->name], $this->alias), $sensor)) === false) {
            return [];
        }

        if ($val instanceof DomainBase) {
            return  $val->expandTypes($availableParams, $n, $val, $sensor, false); 
        }
        else {
            return $this->domain->expandTypes($availableParams, $n, $val, $sensor, false);
        }
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        if (($n = $this->resolveName(array_flip($availableParams), $name, array_merge([$this->name], $this->alias), $sensor)) === false) {
            return [];
        }
        
        if ($val instanceof DomainBase) {
            return  $val->expandValues($availableParams, $n, $val, $sensor, false); 
        }
        else {
            return $this->domain->expandValues($availableParams, $n, $val, $sensor, false);
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
        if (count($this->alias) > 0) {
            $alias = array_flip($this->alias);
            if ($matches = array_intersect_key($results, $alias)) {
                return $results[key($matches)];
            }
        }
        
        if (isset($results[$this->name])) {
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
