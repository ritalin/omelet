<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\Delete;
use Omelet\Annotation\Insert;
use Omelet\Annotation\Select;

/**
 * @Dao(route="/")
 */
interface ConstDao2
{
    /**
     * @Command(returning={"value1"="int(4)", "value2"="int"})
     */
    public function returnAsParam();
}
