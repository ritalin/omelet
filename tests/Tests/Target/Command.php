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
final class Command extends Core\AbstractCommandAnnotation implements Core\DaoAnnotation
{
}
