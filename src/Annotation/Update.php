<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Update.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Update extends Core\AbstractCommandAnnotation implements Core\DaoAnnotation
{
}
