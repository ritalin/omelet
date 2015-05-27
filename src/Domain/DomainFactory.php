<?php

namespace Omelet\Domain;

use Doctrine\DBAL\Types;
use Doctrine\Common\Annotations\AnnotationReader;

use Omelet\Annotation\AnnotationConverterAdapter;
use Omelet\Annotation\Entity;
use Omelet\Annotation\ColumnType;
use Omelet\Annotation\Column;

use Omelet\Util\CaseSensor;

final class DomainFactory {
    private static $alias = [
        'int'       => Types\Type::INTEGER,
        'bool'      => Types\Type::BOOLEAN,
        'double'    => Types\Type::FLOAT,
        '\DateTime' => Types\Type::DATETIME,
        'DateTime' => Types\Type::DATETIME,
    ];

    public function parse($name, $type, CaseSensor $sensor) {
        return $this->parseInternal($name, $type, $sensor, true);
    }

    public function parseInternal($name, $type, CaseSensor $sensor, $hasName) {
        if ($type === null) {
            $type = Types\Type::STRING;
        }
        if (isset(self::$alias[$type])) {
            $type = self::$alias[$type];
        }
        
        if (($p = strrpos($type, '[]')) !== false) {
            return new ArrayDomain($this->parseInternal('', substr($type, 0, $p), $sensor, true));
        }
        
        if ($type === Types\Type::TARRAY) {
            return new ArrayDomain(new BuiltinDomain(Types\Type::STRING));
        }
        
        if (Types\Type::hasType($type)) {
            return new BuiltinDomain($type);
        }
        
        if (is_subclass_of($type, DomainBase::class)) {
            return $this->parseAsDomain($type, $sensor, $hasName);
        }
        
        if (class_exists($type)) {
            return self::parseAsEntity(new \ReflectionClass($type), $sensor);
        }
        
        throw new \Exception("domain not found: ($type $name)");
    }
    
    private function parseParamDomain(array $params, array $annotations, CaseSensor $sensor) {
        $paramNames = array_map(
            function (\ReflectionParameter $p) { return $p->name; },
            $params
        );

        $annotations = array_reduce(
            $annotations,
            function (array &$tmp, ParamAlt $a) use($paramNames) {
                if (in_array($a->name, $paramNames)) {
                    $tmp[$a->name] = $a;
                }
                
                return $tmp;
            },
            array_fill_keys($paramNames, null)
        );

        return array_map(
            function (ParamAlt $a) use($sensor) {
                return $this->parseInternal($a->name, $a->type, $sensor, false);
            },
            $annotations
        )
    }

    public function parseAsDomain($type, CaseSensor $sensor, $hasName) {
        $ref = \ReflectionClass($type);
        $reader = new AnnotationConverterAdapter($ref);

        $ctor = $ref->getConstructor();
        $domains = $this->parseParamDomain($ctor->getParameters(), $reader->getMethodAnnotations($ctor));

        if (! $hasName) {
            return new WrappedDomain($type, array_values($domains));
        }
        else {
            $domains = array_map(
                function ($name) use($domains, $sensor) {
                    if (($m = $this->getGetterMethod($m)) === false) return $domains[$name];

                    $annotations = $reader->getMethodAnnotations($m);

                    list($alias, $default) = $this->extractAnnotation(
                        $annotations, Column::class, function ($a) { 
                            return [ $a->alias, $a->default ]; 
                        }
                    );

                    return new NamedAliasDomain(
                        $domains[$name], $sensor->convert($name), $sensor->convert($alias), $default
                    );
                },
                array_keys($domains)
            );

            return new WrappedDomain($type, $domains);
        }
    }

    private function getGetterMethod(\ReflectionClass $ref, $name) {
        if ($ref->hasMethod($name)) {
            return $ref->getMethod($name);
        }

        $name = 'get' . ucfirst($name);
        if ($ref->hasMethod($name)){
            return $ref->getMethod($name);
        }

        return false;
    }

    public function parseAsEntity(\ReflectionClass $ref, CaseSensor $sensor) {
        $reader = new AnnotationConverterAdapter($ref);
        
        $fields = array_reduce(
            $ref->getProperties(),
            function (array &$tmp, \ReflectionProperty $f) use($reader, $sensor) {
                $annotations = $reader->getPropertyAnnotations($f);
                
                $columnType = $this->extractAnnotation($annotations, ColumnType::class, function ($a) { return $a->type; } );                
                list($alias, $default, $optFields) = $this->extractAnnotation(
                    $annotations, Column::class, function ($a) { 
                        return [ $a->alias, $a->default, $a->optFields ]; 
                    }
                );
                $domain = $this->parseInternal($f->name, $columnType, $sensor, false);
                
                $optFields = array_map(
                    function ($name) use($sensor) { return $sensor->convert($name); },
                    $optFields !== null ? $optFields : []
                );
                
                return $tmp + [$f->name => new NamedAliasDomain(
                    $domain, $sensor->convert($f->name), $sensor->convert($alias), $default, 
                    array_flip($optFields)
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
