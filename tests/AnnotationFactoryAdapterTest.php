<?php

namespace OmeletTests;

use Doctrine\Common\Annotations\PhpParser;

use Omelet\Annotation\AnnotationFactory;

use Omelet\Tests\Target\TodoDao3;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

class AnnotationFactoryAdapterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_parse_returning_premetive() {
     	$intf = new \ReflectionClass(TodoDao3::class);

     	$classParser = new PhpParser;
        $commentParser = new AnnotationFactory(
            $classParser->parseClass($intf) + [$intf->getNamespaceName()]
        );

        $annotations = $commentParser->getMethodAnnotations($intf->getMethod('listByPub'));

//        var_dump($annotations);
     }

     public function test_1() {
        $context = new DaoBuilderContext();
        $builder = new DaoBuilder(new \ReflectionClass(TodoDao3::class), $context->getDaoClassName(TodoDao3::class));
                
//        $builder->prepare();
        $methods = $builder->getMethods();

     }
}