<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Delete;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Select;

/**
 * @Dao(route="/")
 */
interface ConstDao
{
    /**
     * @Select
     *
     * @return Editor
     */
    public function getEditorConst();

    /**
     * @Select
     *
     * @return Timestamp
     */
    public function now();

    /**
     * @Select
     *
     * @return Hidden
     */
    public function hidden(Hidden $hidden);
}
