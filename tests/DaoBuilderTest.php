<?php

namespace OmeletTests;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;
use Omelet\Tests\Target\TodoDao;

class DaoBuilderTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_build() {
        $context = new DaoBuilderContext();
        
        $builder = new DaoBuilder(new \ReflectionClass(TodoDao::class), $context->getDaoClassName(TodoDao::class));
        
        $this->assertEquals('Omelet\Tests\Target\TodoDao', $builder->getInterfaceName());
        $this->assertEquals('Omelet\Tests\Target\TodoDaoImpl', $builder->getClassName());
        
        $this->assertCount(0, $builder->getMethods());
        
        $builder->prepare();
        $methods = $builder->getMethods();
        
        $this->assertCount(6, $builder->getMethods());
        
        select1: {
            $this->assertArrayHasKey('listAll', $methods);
            
            $info = $methods['listAll'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('listAll', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(0, $info['params']);
        }
        select2: {
            $this->assertArrayHasKey('findById', $methods);
            
            $info = $methods['findById'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('findById', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(1, $info['params']);
        }
        select3: {
            $this->assertArrayHasKey('listByPub', $methods);
            
            $info = $methods['listByPub'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('listByPub', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(2, $info['params']);
        }
        insert: {
            $this->assertArrayHasKey('insert', $methods);
            
            $info = $methods['insert'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('insert', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Insert', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(1, $info['params']);
        }
        update: {
            $this->assertArrayHasKey('update', $methods);
            
            $info = $methods['update'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('update', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Update', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(1, $info['params']);
        }
        delete: {
            $this->assertArrayHasKey('delete', $methods);
            
            $info = $methods['delete'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('delete', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Delete', $info['type']);
            
            $this->assertArrayHasKey('params', $info);
            $this->assertCount(1, $info['params']);
        }
        
    }
}
