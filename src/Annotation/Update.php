<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Update
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Update implements DaoAnnotation {}
