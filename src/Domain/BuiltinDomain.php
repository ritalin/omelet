<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Omelet\Util\CaseSensor;

class BuiltinDomain extends DomainBase
{
    private $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    protected function expandTypesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return [$name => Type::getType($this->type)];
    }

    protected function expandValuesInternal(array $availableParams, $name, $val, CaseSensor $sensor)
    {
        return [$name => $val];
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform, CaseSensor $sensor)
    {
        if (is_array($results)) {
            $results = current($results);
        }

        return Type::getType($this->type)->convertToPHPValue($results, $platform);
    }

    public static function __set_state($values)
    {
        return new BuiltinDomain($values['type']);
    }
}
