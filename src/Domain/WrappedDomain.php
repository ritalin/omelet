<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class WrappedDomain extends DomainBase
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var array
     */
    private $fieldDomains;

    public function __construct($type, array $fieldDomains)
    {
        $this->type = $type;
        $this->fieldDomains = $fieldDomains;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function expandTypesInternal($name, $val, CaseSensor $sensor)
    {
        return ($val instanceof CustomDomain) ? $val->expandTypes($name, $val, $sensor, false) : [];
    }

    protected function expandValuesInternal($name, $val, CaseSensor $sensor)
    {
        return current($this->fieldDomains)->expandValues($name, $val, $sensor, false);
//        return ($val instanceof CustomDomain) ? $val->expandValues($name, $val, $sensor, false) : [];
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform)
    {
        if (is_array($results) && is_int(key($results))) {
            $results = current($results);
        }

        $ref = new \ReflectionClass($this->type);
        if (is_array($results)) {
            if (is_int(key($results))) {
                $domains = array_values($this->fieldDomains);
                $values = array_map(
                    function ($i) use ($results, $domains, $platform) {
                        return isset($results[$i]) ? $domains[$i]->convertResults([ $results[$i] ], $platform) : null;
                    },
                    range(0, count($domains))
                );
            }
            else {
                $domains = $this->fieldDomains;
                $values = array_map(
                    function ($name) use ($results, $domains, $platform) {
                        return isset($results[$name]) ? $domains[$name]->convertResults($results, $platform) : null;
                    },
                    array_keys($domains)
                );
            }
            return $ref->newInstanceArgs($values);
        }
        else {
            if (($d = current($this->fieldDomains)) === false) {
                $value = $results;
            }
            else {
                $value = $d->convertResults([ $results ], $platform);
            }

            return $ref->newInstance($value);
        }
    }

    public static function __set_state($values)
    {
        return new WrappedDomain($values['type'], $values['fieldDomains']);
    }
}
