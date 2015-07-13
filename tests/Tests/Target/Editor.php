<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;
use Omelet\Domain\CustomDomain;

class Editor extends CustomDomain
{
    public function __construct($id, $name = '')
    {
        parent::__construct(Type::INTEGER, $id, ['name' => $name]);
    }

    public function getId()
    {
        return $this->getValue();
    }

    public function getName()
    {
        return $this->getOptValue('name');
    }

    public function name()
    {
        return $this->getOptValue('name');
    }
}
