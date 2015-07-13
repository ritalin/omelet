<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Dao;
use Omelet\Annotation\SequenceHint;

/**
 * @Dao(route="/")
 * @SequenceHint(table="todo")
 */
interface TodoDao3
{
}
