<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ParamAlt
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $name;
    
    public static function __set_state($values)
    {
        $a = new self();
        $a->type = $values['type'];
        $a->name = $values['name'];

        return $a;
    }
}
