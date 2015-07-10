<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class ComplexDomain extends DomainBase
{
    private $domains;
    private $boundOneArray = false;

    public function __construct(array $domains)
    {
        if ((count($domains) === 1) && (current(array_values($domains)) instanceof ArrayDomain)) {
            $this->boundOneArray = true;
        }

        $this->domains = $domains;
    }

    public function getChildren()
    {
        return $this->domains;
    }

    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use ($availableParams, $val, $sensor) {
                $n = $this->boundOneArray ? '' : $k;

                return $tmp + [$k => $this->domains[$k]->expandTypes($availableParams, $n, $val[$k], $sensor, false)];
            },
            []
        );
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use ($availableParams, $val, $sensor) {
                $n = $this->boundOneArray ? '' : $k;

                return $tmp + [$k => $this->domains[$k]->expandValues($availableParams, $n, $val[$k], $sensor, false)];
            },
            []
        );
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform)
    {
        return $results;
    }

    public static function __set_state($values)
    {
        return new ComplexDomain($values['domains']);
    }
}
