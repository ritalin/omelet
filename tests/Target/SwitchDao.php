<?php

namespace Omelet\Tests\Target;

/**
 * @Dao(route="/")
 */
interface SwitchDao {
    /**
     * @Select
     */
    function findA();
}
