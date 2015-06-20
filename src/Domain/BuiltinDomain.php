<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

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

    protected function expandTypesInternal($name, $val)
    {
        return [$name => Type::getType($this->type)];
    }

    protected function expandValuesInternal($name, $val)
    {
        return [$name => $val];
    }

    protected function convertResultsInternal($results, AbstractPlatform $platform)
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
