<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types;
use Doctrine\Common\Annotations\AnnotationReader;

use Omelet\Annotation\AnnotationConverterAdapter;
use Omelet\Annotation\Entity;
use Omelet\Annotation\ColumnType;
use Omelet\Annotation\Column;

final class DomainFactory {
    private static $alias = [
        'int'       => Types\Type::INTEGER,
        'bool'      => Types\Type::BOOLEAN,
        'double'    => Types\Type::FLOAT,
        '\DateTime' => Types\Type::DATETIME,
        'DateTime' => Types\Type::DATETIME,
    ];

    public function parse($name, $type) {
        if ($type === null) {
            $type = Types\Type::STRING;
        }
        if (isset(self::$alias[$type])) {
            $type = self::$alias[$type];
        }
        
        if (($p = strrpos($type, '[]')) !== false) {
            return new ArrayDomain($this->parse('', substr($type, 0, $p)));
        }
        
        if ($type === Types\Type::TARRAY) {
            return new ArrayDomain(new BuiltinDomain(Types\Type::STRING));
        }
        
        if (Types\Type::hasType($type)) {
            return new BuiltinDomain($type);
        }
        
        if (is_subclass_of($type, DomainBase::class)) {
            return new WrappedDomain($type);
        }
        
        if (class_exists($type)) {
            return self::parseAsEntity(new \ReflectionClass($type));
        }
        
        throw new \Exception("domain not found: ($type $name)");
    }
    
    public function parseAsEntity(\ReflectionClass $ref) {
        $reader = new AnnotationConverterAdapter($ref);
        
        $fields = array_reduce(
            $ref->getProperties(),
            function (array &$tmp, \ReflectionProperty $f) use($reader) {
                $annotations = $reader->getPropertyAnnotations($f);
                
                $columnType = $this->extractAnnotation($annotations, ColumnType::class, function ($a) { return $a->type; } );                
                list($alias, $default, $optFields) = $this->extractAnnotation(
                    $annotations, Column::class, function ($a) { return [ $a->alias, $a->default, $a->optFields ]; }
                );
                $domain = $this->parse($f->name, $columnType);

                return $tmp + [$f->name => new NamedAliasDomain(
                    $domain, $f->name, $alias, $default, 
                    array_flip($optFields !== null ? $optFields : [])
                )];
            },
            []
        );
        
        return new ObjectDomain($ref->name, $fields);
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
    
    private function extractAnnotation(array $annotations, $class, callable $fn) {
        $tmp = array_filter(
            $annotations,
            function ($a) use($class) {
                return $a instanceof $class;
            }
        );
        
        return count($tmp) > 0 ? $fn(array_shift($tmp)) : null;
    }
}
