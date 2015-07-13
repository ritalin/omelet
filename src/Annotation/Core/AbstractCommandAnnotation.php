<?php

namespace Omelet\Annotation\Core;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Insert.
 */
class AbstractCommandAnnotation implements DaoAnnotation
{
    /**
     * var string[]
     */
    public $returning;
}
