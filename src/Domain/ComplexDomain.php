<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Platforms\AbstractPlatform;

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

    protected function expandTypesInternal($name, $val)
    {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use ($val) {
                $n = $this->boundOneArray ? '' : $k;

                return $tmp + [$k => $this->domains[$k]->expandTypes($n, $val[$k])];
            },
            []
        );
    }

    protected function expandValuesInternal($name, $val)
    {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use ($val) {
                $n = $this->boundOneArray ? '' : $k;

                return $tmp + [$k => $this->domains[$k]->expandValues($n, $val[$k])];
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
