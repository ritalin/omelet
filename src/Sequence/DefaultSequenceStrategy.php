<?php

namespace Omelet\Sequence;

use Omelet\Annotation\SequenceHint;

class DefaultSequenceStrategy implements SequenceNameStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(SequenceHint $hint)
    {
        if ($hint !== null) {
            if ($hint->name !== null) {
                return $hint->name;
            }
            elseif (($hint->table !== null) && ($hint->column !== null)) {
                return "{$hint->table}_{$hint->column}_seq";
            }
        }
        
        return null;
    }
}
