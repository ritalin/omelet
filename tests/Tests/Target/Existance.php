<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;
use Omelet\Domain\CustomDomain;

class Existance extends CustomDomain
{
    public function __construct($value)
    {
        parent::__construct(Type::BOOLEAN, (bool)$value);
    }
}
