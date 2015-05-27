<?php

namespace Omelet\Tests\Target;

use Doctrine\DBAL\Types\Type;

use Omelet\Domain\CustomDomain;

class Editor extends CustomDomain {
	/**
	 * @param integer editorId
	 * @param string editorName
	 */
    public function __construct($editorId, $editorName) {
        parent::__construct(Type::BOOLEAN, $editorId);
    }
}
