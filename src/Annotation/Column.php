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
    public $alias;
    
    public static function __set_state($values) {
        $a = new self;
        $a->alias = $values['alias'];
        
        return $a;
    }
}
