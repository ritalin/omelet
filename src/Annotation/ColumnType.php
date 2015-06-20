<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Column.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class ColumnType implements Core\EntityFieldAnnotation
{
    public $type;
    public $name;

    public static function __set_state($values)
    {
        $a = new self();
        $a->type = $values['type'];
        $a->name = $values['name'];

        return $a;
    }
}
