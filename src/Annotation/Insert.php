<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Insert.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Insert extends Core\AbstractCommandAnnotation implements Core\DaoAnnotation
{
}
