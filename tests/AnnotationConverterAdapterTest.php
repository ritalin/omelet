<?php

namespace OmeletTests;

use Omelet\Annotation\AnnotationConverterAdapter;

use Omelet\Tests\Target\TodoDao2;
use Omelet\Tests\Target\Todo;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

use Omelet\Annotation\Select;
use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\Returning;

class AnnotationConverterAdapterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_parse_returning_premetive() {
        $intf = new \ReflectionClass(TodoDao2::class);

        $commentParser = new AnnotationConverterAdapter($intf);

        $annotations = $commentParser->getMethodAnnotations($intf->getMethod('listByPub'));
        
        $this->assertCount(4, $annotations);
        $this->assertInstanceOf(Select::class, $annotations[0]);
        $this->assertInstanceOf(ParamAlt::class, $annotations[1]);
        $this->assertEquals(\DateTime::class, $annotations[1]->type);
        $this->assertEquals('from', $annotations[1]->name);
        $this->assertInstanceOf(ParamAlt::class, $annotations[2]);
        $this->assertEquals(\DateTime::class, $annotations[2]->type);
        $this->assertEquals('to', $annotations[2]->name);
        $this->assertInstanceOf(Returning::class, $annotations[3]);
        $this->assertEquals(Todo::class . '[]', $annotations[3]->type);
     }
}
