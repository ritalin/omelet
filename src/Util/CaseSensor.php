<?php

namespace Omelet\Util;

use Camel\Format;

final class CaseSensor
{
    private static $formatters = [];

    public static function LowerSnake()
    {
        return new self(Format\SnakeCase::class);
    }

    public static function UpperSnake()
    {
        return new self(Format\ScreamingSnakeCase::class);
    }

    public static function LowerCamel()
    {
        return new self(Format\CamelCase::class);
    }

    public static function UpperCamel()
    {
        return new self(Format\StudlyCaps::class);
    }

    private static function getFormatter($class)
    {
        if (! isset(self::$formatters[$class])) {
            $f = new $class();

            self::$formatters[$class] = $f;
        }
        else {
            $f = self::$formatters[$class];
        }

        return $f;
    }

    /**
     * @Format\FormatInterface
     */
    private $formatter;

    private function __construct($formatterClass)
    {
        $this->formatter = self::getFormatter($formatterClass);
    }

    public function convert(...$inputs)
    {
        $tokens = [];
        foreach ($inputs as $input) {
            $tokens[] = $this->getSplitter($input)->split($input);
        }

        return $this->formatter->join(call_user_func_array('array_merge', $tokens));
    }
    
    private function getSplitter($input)
    {
        if (strpos($input, '_') !== false) {
            return self::getFormatter(Format\SnakeCase::class);
        }
        else {
            return self::getFormatter(Format\CamelCase::class);
        }
    }
}
