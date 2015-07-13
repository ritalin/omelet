<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\SequenceHint;
use Omelet\Annotation\Insert;

/**
 * @Dao(route="/")
 * @SequenceHint(table="todo")
 */
interface TodoDao3
{
}
