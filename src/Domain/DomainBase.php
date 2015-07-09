<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

abstract class DomainBase
{
    abstract protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor);
    abstract protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor);
    abstract protected function convertResultsInternal($results, AbstractPlatform $platform);

    public function expandTypes(array $availableParams, $name, $val, CaseSensor $sensor, $root = true)
    {
        $types = $this->expandTypesInternal($availableParams, $name, $val, $sensor);

        if ($root) {
            $types = $this->flatten($types);
        }
        
        return $types;
    }

    public function expandValues(array $availableParams, $name, $val, CaseSensor $sensor, $root = true)
    {
        $values = $this->expandValuesInternal($availableParams, $name, $val, $sensor);

        if ($root) {
            $values = $this->flatten($values);
        }
        
        return $values;
    }

    public function convertResults($results, AbstractPlatform $platform)
    {
        return $this->convertResultsInternal($results, $platform);
    }

    private function flatten(array $a)
    {
        return array_reduce(
            array_keys($a),
            function (array &$tmp, $k) use ($a) {
                return is_array($a[$k]) ? $tmp + $this->flatten($a[$k]) : $tmp + [$k => $a[$k]];
            },
            []
        );
    }
}
