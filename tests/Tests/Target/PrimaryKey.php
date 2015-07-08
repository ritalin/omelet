<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;

class PrimaryKey extends CustomDomain
{
    public function __construct($value)
    {
        parent::__construct(Type::INTEGER, $value);
    }
}
