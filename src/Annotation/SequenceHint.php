<?php

namespace Omelet\Annotation;

/**
 * @Annotation
 * @Target("METHOD")

 */
class SequenceHint
{
    /**
     * Target table name.
     *
     * @var string
     */
    public $table;
    /**
     * Key column field.
     *
     * @var string
     */
    public $column;
    /**
     * Sequence name.
     *
     * @var $name
     */
    public $name;
}
