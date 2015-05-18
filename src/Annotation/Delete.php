<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Delete
 *
 * @Annotation
 * @Target("METHOD")
 */
 final class Delete implements DaoAnnotation {}
