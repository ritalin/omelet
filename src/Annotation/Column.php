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
final class Column {
    public $type;
    public $name;
}
