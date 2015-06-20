<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;
use Omelet\Domain\CustomDomain;

class Editor extends CustomDomain
{
    public function __construct($editorId, $editorName = '')
    {
        parent::__construct(Type::INTEGER, $editorId, ['name' => $editorName]);
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
