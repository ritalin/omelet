<?php

namespace Omelet\Sequence;

use Omelet\Annotation\SequenceHint;

class SqliteSequenceStrategy implements SequenceNameStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(SequenceHint $hint = null)
    {
        return $hint !== null ? $hint->table : null;
    }
}
