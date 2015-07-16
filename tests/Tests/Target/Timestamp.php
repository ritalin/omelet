<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;
use Omelet\Annotation\Alias;

class Timestamp extends CustomDomain
{
    /**
     * @Alias(name="value", alias="value1")
     */
    public function __construct(\DateTime $value)
    {
        parent::__construct(Type::DATETIME, $value);
    }
}
