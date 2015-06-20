<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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

    protected function expandTypesInternal($name, $val)
    {
        return $this->domain->expandTypes($name, $val);
    }

    protected function expandValuesInternal($name, $val)
    {
        return $this->domain->expandValues($name, $val);
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
            $value = $results[$this->alias];
        }
        elseif (isset($results[$this->name])) {
            $value = $results[$this->name];
        }

        if ($value === null) {
            $value =  $this->default;
        }

        return $value;
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
