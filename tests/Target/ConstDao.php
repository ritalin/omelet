<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Update;
use Omelet\Annotation\Delete;

use Omelet\Annotation\ParamAlt;

/**
 * @Dao(route="/")
 */

interface ConstDao {
    /**
     * @Select
     *
     * @return Editor
     */
    function getEditorConst();
}
