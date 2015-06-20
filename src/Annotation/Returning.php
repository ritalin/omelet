<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Returning.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Returning implements DaoAnnotation
{
    /**
     * @var string
     */
    public $type;

    public static function __set_state($values)
    {
        $a = new self();
        $a->type = $values['type'];

        return $a;
    }
}
