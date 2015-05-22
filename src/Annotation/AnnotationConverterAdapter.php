<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationConverterAdapter {
    /**
     * @var ReflectionClass
     */
    private $intf;
    
    /**
     * @var AnnotationReader 
     */
    private $reader;
    /**
     * @var AnnotationConverter
     */
    private $converter;
    
    public function __construct(\ReflectionClass $intf) {
        $this->intf = $intf;
        
        $classParser = new PhpParser;
        $uses = array_merge($classParser->parseClass($this->intf), [$this->intf->getNamespaceName()]);
        
        $this->reader = new AnnotationReader($this->intf);
        $this->converter = new AnnotationConverter($uses);
    }
    
    /**
     * Collect method annotations
     *
     * @param ReflectionMethod methodName
     * @return DaoAnnotation[]
     */
    public function getMethodAnnotations(\ReflectionMethod $method) {
        return array_merge(
            $this->reader->getMethodAnnotations($method),
            $this->converter->getMethodAnnotations($method)
        );
    }
    
    /**
     * Collect property annotations
     *
     * @param ReflectionProperty methodName
     * @return EntityFieldAnnotation[]
     */
    public function getPropertyAnnotations(\ReflectionProperty $prop) {
        return array_merge(
            $this->reader->getPropertyAnnotations($prop),
            $this->converter->getPropertyAnnotations($prop)
        );
    }
    
    public function getClassAnnotations() {
        return $this->reader->getClassAnnotations($this->intf);
    }
}
