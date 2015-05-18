<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Select
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Select implements DaoAnnotation {}
