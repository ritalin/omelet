<?php

namespace OmeletTests;

use Omelet\Annotation\AnnotationConverterAdapter;

use Omelet\Tests\Target\TodoDao2;
use Omelet\Tests\Target\Todo;
use Omelet\Tests\Target\Hidden;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

use Omelet\Annotation\Select;
use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\Returning;
use Omelet\Annotation\Column;

class AnnotationConverterAdapterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_parse_method_annotation() {
        $intf = new \ReflectionClass(TodoDao2::class);

        $commentParser = new AnnotationConverterAdapter($intf);
        
        listByPub: {
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
     
    /**
     * @test
     */
     public function test_parse_property_annotation() {
        $intf = new \ReflectionClass(Todo::class);
        
        $commentParser = new AnnotationConverterAdapter($intf);
        
        id: {
            $annotations = $commentParser->getPropertyAnnotations($intf->getProperty('id'));
            
            $this->assertCount(1, $annotations);
            $this->assertInstanceOf(Column::class, $annotations[0]);
            $this->assertEquals('integer', $annotations[0]->type);
            $this->assertEquals('id', $annotations[0]->name);
        }
        created: {
            $annotations = $commentParser->getPropertyAnnotations($intf->getProperty('created'));
            
            $this->assertCount(1, $annotations);
            $this->assertInstanceOf(Column::class, $annotations[0]);
            $this->assertEquals(\DateTime::class, $annotations[0]->type);
            $this->assertEquals('created', $annotations[0]->name);
        }
        hidden: {
            $annotations = $commentParser->getPropertyAnnotations($intf->getProperty('hidden'));
            
            $this->assertCount(1, $annotations);
            $this->assertInstanceOf(Column::class, $annotations[0]);
            $this->assertEquals(Hidden::class, $annotations[0]->type);
            $this->assertEquals('hidden', $annotations[0]->name);
        }
    }
}
