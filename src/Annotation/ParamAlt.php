<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ParamAlt {
    public $type;
    public $name;
    
    public static function __set_state($values) {
        $a = new ParamAlt;
        $a->type = $values['type'];
        $a->name = $values['name'];
        
        return $a;
    }
}
