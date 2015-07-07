<?php

namespace Omelet\Sequence;

use Omelet\Annotation\SequenceHint;

interface SequenceNameStrategyInterface
{
    /**
     * @param SequenceHint hint
     *
     * @return string
     */
    public function resolve(SequenceHint $hint);
}
