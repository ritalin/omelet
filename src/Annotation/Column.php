<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Column.
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Column implements Core\EntityFieldAnnotation
{
    /**
     * @var string
     */
    public $alias;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var string[]
     */
    public $optFields = [];

    public static function __set_state($values)
    {
        $a = new self();
        $a->alias = $values['alias'];
        $a->default = $values['default'];
        $a->optFields = $values['optFields'];

        return $a;
    }
}
