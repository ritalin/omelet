<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Column
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Column implements Core\EntityFieldAnnotation {
    public $type;
    public $name;
    
    public static function __set_state($values) {
        $a = new self;
        $a->type = $values['type'];
        $a->name = $values['name'];
        
        return $a;
    }
}
