<?php

namespace Omelet\Util;

use Camel\Format;

final class CaseSensor {
    private static $formatters = [];

    public static function LowerSnake() {
        return new self(self::getFormatter(Format\SnakeCase::class));
    }
    
    public static function UpperSnake() {
        return new self(self::getFormatter(Format\ScreamingSnakeCase::class));
    }
    
    public static function LowerCamel() {
        return new self(self::getFormatter(Format\CamelCase::class));
    }
    
    public static function UpperCamel() {
        return new self(self::getFormatter(Format\StudlyCaps::class));
    }
    
    private static function getFormatter($class) {
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
    
    public function __construct(Format\FormatInterface $formatter) {
        $this->formatter = $formatter;
    }
    
    public function convert($input) {
        $tokens = $this->getSplitter($input)->split($input);
        
        return $this->formatter->join($tokens);
    }
    
    private function getSplitter($input) {
        if (strpos($input, '_') !== false) {
            return self::getFormatter(Format\SnakeCase::class);
        }
        else {
            return self::getFormatter(Format\CamelCase::class);
        }
    }
}
