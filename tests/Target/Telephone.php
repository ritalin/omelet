<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;

class Telephone extends CustomDomain {
    public function __construct($tel) {
        parent::__construct(Type::STRING, $tel);
    }
}
