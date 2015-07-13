<?php

namespace Omelet\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
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
     * @var string
     */
    public $name;

    public static function __set_state(array $values)
    {
        $result = new SequenceHint();
        $result->table = $values['table'];
        $result->column = $values['column'];
        $result->name = $values['name'];

        return $result;
    }
}
