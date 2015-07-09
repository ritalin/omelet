<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;
use Omelet\Annotation\Alias;

class Hidden extends CustomDomain
{
    /**
     * @Alias(name="value", alias="state")
     *
     * @param integer value
     */
    public function __construct($value)
    {
        parent::__construct(Type::BOOLEAN, $value);
    }
}
