<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;

class Timestamp extends CustomDomain
{
    public function __construct(\DateTime $value)
    {
        parent::__construct(Type::DATETIME, $value);
    }
}
