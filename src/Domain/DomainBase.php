<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class DomainBase
{
    abstract protected function expandTypesInternal($name, $val);
    abstract protected function expandValuesInternal($name, $val);
    abstract protected function convertResultsInternal($results, AbstractPlatform $platform);

    public function expandTypes($name, $val, $root = true)
    {
        $types = $this->expandTypesInternal($name, $val);

        return $root ? $this->flatten($types) : $types;
    }

    public function expandValues($name, $val, $root = true)
    {
        $values = $this->expandValuesInternal($name, $val);

        return $root ? $this->flatten($values) : $values;
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
