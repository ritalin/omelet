<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types;
use Doctrine\Common\Annotations\AnnotationReader;

use Omelet\Annotation\Entity;
use Omelet\Annotation\Column;

final class DomainFactory {
    private static $alias = [
        'int'       => Types\Type::INTEGER,
        'bool'      => Types\Type::BOOLEAN,
        'double'    => Types\Type::FLOAT,
        '\DateTime' => Types\Type::DATETIME,
    ];

    public function parse($name, $type, AnnotationReader $reader) {
        if (isset(self::$alias[$type])) {
            $type = self::$alias[$type];
        }
        
        if (($p = strrpos($type, '[]')) !== false) {
            return new ArrayDomain($this->parse('', substr($type, 0, $p), $reader));
        }
        
        if ($type === Types\Type::TARRAY) {
            return new ArrayDomain(new BuiltinDomain(Types\Type::STRING));
        }
        
        if (Types\Type::hasType($type)) {
            return new BuiltinDomain($type);
        }
        
        if (is_subclass_of($type, DomainBase::class)) {
            return new WrappedDomain();
        }
        
        if (class_exists($type)) {
            $ref = new \ReflectionClass($type);
            $attrs = $reader->getClassAnnotations($ref);
            
            if ($this->isEntity($attrs)) {
                return self::parseAsEntity($name, $ref, $reader);
            }
        }
        
        throw new \Exception("domain not found: ($type $name)");
    }
    
    public function parseAsEntity($name, \ReflectionClass $ref, AnnotationReader $reader) {
        $fields = array_reduce(
            $ref->getProperties(),
            function (array &$tmp, \ReflectionProperty $f) use($reader) {
                return $tmp + [$f->name => $this->parse($f->name, $this->fieldType($f, $reader), $reader)];
            },
            []
        );
        
        return new ObjectDomain($fields);
    }
    
    private function isEntity(array $attrs) {
        $tmp = array_filter(
            $attrs,
            function ($a) {
                return $a instanceof Entity;
            }
        );
        
        return count($tmp) > 0;
    }
    
    private function fieldType(\ReflectionProperty $field, AnnotationReader $reader) {
        $tmp = array_filter(
            $reader->getPropertyAnnotations($field),
            function ($a) {
                return $a instanceof Column;
            }
        );
        
        return count($tmp) > 0 ? array_shift($tmp)->type : Type::STRING;
    }
}
