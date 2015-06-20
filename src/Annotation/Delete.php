<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Delete.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Delete implements Core\DaoAnnotation
{
}
