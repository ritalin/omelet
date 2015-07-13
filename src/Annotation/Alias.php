<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Column.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 */
final class Alias implements Core\EntityFieldAnnotation
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string[]
     */
    public $alias;

    public static function __set_state($values)
    {
        $a = new self();
        $a->name = $values['name'];
        $a->alias = $values['alias'];

        return $a;
    }
}
