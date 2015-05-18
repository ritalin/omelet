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
}
