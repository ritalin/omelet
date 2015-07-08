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

    protected function expandTypesInternal($name, $val, CaseSensor $sensor)
    {
        if (is_array($val) && count($val) === 0) {
            return [];
        }

        return $this->expand($name, $val, $sensor,
            function ($k, $v) use ($sensor) { return $this->child->expandTypes($k, $v, $sensor); }
        );
    }

    protected function expandValuesInternal($name, $val, CaseSensor $sensor)
    {
        if (is_array($val) && count($val) === 0) {
            return [];
        }

        return $this->expand($name, $val, $sensor,
            function ($k, $v) use ($sensor) { return $this->child->expandValues($k, $v, $sensor); }
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

    protected function convertResultsInternal($results, AbstractPlatform $platform)
    {
        if (($this->child instanceof BuiltinDomain) && ($this->child->getType() === Type::STRING)) {
            return $results;
        }

        return array_reduce(
            array_keys($results),
            function (array &$tmp, $k) use ($results, $platform) {
                return $tmp + [$k => $this->child->convertResults($results[$k], $platform)];
            },
            []
        );
    }

    public static function __set_state($values)
    {
        return new ArrayDomain($values['child']);
    }
}
