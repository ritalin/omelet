<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Select;

/**
 * @Dao(route="/")
 */
interface SwitchDao
{
    /**
     * @Select
     */
    public function findA();
}
