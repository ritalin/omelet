<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Insert
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Insert implements DaoAnnotation {}