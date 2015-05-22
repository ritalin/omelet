<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Column
 *
 * @Annotation
 * @Target("CLASS")
 */
final class Dao implements Core\DaoAnnotation {
    /**
     * lterntive query access route (uses insted of nmespce)
     *
     * @var string
     */
    public $route = '';
    
    public static function __set_state($values) {
        $a = new self;
        $a->type = $values['route'];
        
        return $a;
    }
}
