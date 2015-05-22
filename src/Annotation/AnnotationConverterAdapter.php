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
     * @param string methodName
     */
    public function getMethodAnnotations(\ReflectionMethod $method) {
        return array_merge(
            $this->reader->getMethodAnnotations($method),
            $this->converter->getMethodAnnotations($method)
        );
    }
}
