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
	/**
	 * @var string
	 */
    public $alias;
    
    /**
     * @var mixed
     */
    public $default;

    public static function __set_state($values) {
        $a = new self;
        $a->alias = $values['alias'];
        $a-> default= $values['default'];
        
        return $a;
    }
}
