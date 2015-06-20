<?php

namespace Omelet\Tests\Target\SwitchDao;

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
    public function findB();
}
