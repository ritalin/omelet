<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class VoidDomain extends DomainBase
{
    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return [$sensor->convert($name) => null];
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return [$sensor->convert($name) => null];
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform, CaseSensor $sensor)
    {
        return null;
    }

    public static function __set_state($values)
    {
        return new VoidDomain();
    }
}
