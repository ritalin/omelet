<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use Omelet\Util\CaseSensor;

class ArrayDomain extends DomainBase
{
    /**
     * @var DomainBase
     */
    private $child;

    public function __construct(DomainBase $child)
    {
        $this->child = $child;
    }

    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        if (is_array($val) && count($val) === 0) {
            return [];
        }

        return $this->expand($name, $val, $sensor,
            function ($k, $v) use ($availableParams, $sensor) {
                return $this->child->expandTypes($availableParams, $k, $v, $sensor, false);
            }
        );
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        if (is_array($val) && count($val) === 0) {
            return [];
        }

        return $this->expand($name, $val, $sensor,
            function ($k, $v) use ($availableParams, $sensor) {
                return $this->child->expandValues($availableParams, $k, $v, $sensor, false);
            }
        );
    }

    private function expand($name, $val, CaseSensor $sensor, callable $fn)
    {
        return array_reduce(
            array_keys($val),
            function (array &$tmp, $k) use ($name, $val, $sensor, $fn) {
                $n = $name !== '' ? $sensor->convert($name, $k) : $k;

                return $tmp + [$k => $fn($n, $val[$k])];
            },
            []
        );
    }

    public function childDomain()
    {
        return $this->child;
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform, CaseSensor $sensor)
    {
        return array_map(
            function ($v) use ($platform, $sensor) {
                return $this->child->convertResults($v, $platform, $sensor);
            },
            $results
        );
    }
    
    private function convertResultBuiltin($results, AbstractPlatform $platform, CaseSensor $sensor)
    {
        return array_reduce(
            array_keys($results),
            function (array &$tmp, $k) use ($results, $platform, $sensor) {
                $n = $sensor->convert($k);
                
                return $tmp + [$n => $this->child->convertResults($results[$k], $platform, $sensor)];
            },
            []
        );
    }
    
    public static function __set_state($values)
    {
        return new ArrayDomain($values['child']);
    }
}
