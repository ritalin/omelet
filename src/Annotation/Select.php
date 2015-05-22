<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Select
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Select implements Core\DaoAnnotation {}
