<?php

namespace Omelet\Tests\Target;

use Doctrine\Common\Annotations\Annotation;
use Omelet\Annotation\Core\AbstractCommandAnnotation;
use Omelet\Annotation\Core\DaoAnnotation;

/**
 * Insert.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Command extends AbstractCommandAnnotation implements DaoAnnotation
{
}
