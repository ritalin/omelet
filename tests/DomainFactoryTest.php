<?php

namespace Omelet\Tests;

use Doctrine\DBAL\Types;
use Doctrine\DBAL\Types\Type;
use Doctrine\Common\Annotations\AnnotationReader;

use Omelet\Domain;
use Omelet\Domain\DomainFactory;

use Omelet\Tests\Target\Telephone;
use Omelet\Tests\Target\Todo;
use Omelet\Tests\Target\Hidden;

class DomainFactoryTest extends \PHPUnit_Framework_TestCase {
    /**
     * @Test
     */
    public function test_built_in_domain() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
        
        $defs = $factory->parse('bbb', 'integer', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::INTEGER, $defs->getType());
        $this->assertEquals(['bbb' => Type::INTEGER], $defs->expandTypes('bbb', 123));
        $this->assertEquals(['bbb' => 123], $defs->expandValues('bbb', 123));
        
        $defs = $factory->parse('bbb', 'boolean', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::BOOLEAN, $defs->getType());
        $this->assertEquals(['bbb' => Type::BOOLEAN], $defs->expandTypes('bbb', false));
        $this->assertEquals(['bbb' => false], $defs->expandValues('bbb', false));
        
        $defs = $factory->parse('bbb', 'float', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::FLOAT, $defs->getType());
        $this->assertEquals(['bbb' => Type::FLOAT], $defs->expandTypes('bbb', 98.7));
        $this->assertEquals(['bbb' => 98.7], $defs->expandValues('bbb', 98.7));
        
        $defs = $factory->parse('bbb', 'string', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::STRING, $defs->getType());
        $this->assertEquals(['bbb' => Type::STRING], $defs->expandTypes('bbb', 'qwerty'));
        $this->assertEquals(['bbb' => 'qwerty'], $defs->expandValues('bbb', 'qwerty'));
    }
    
    /**
     * @test
     */
    public function test_built_in_domain_alias() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
    
        $defs = $factory->parse('aaa', 'int', $reader);
        
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::INTEGER, $defs->getType());
        $this->assertEquals(['aaa' => Type::INTEGER], $defs->expandTypes('aaa', 123));
        $this->assertEquals(['aaa' => 123], $defs->expandValues('aaa', 123));
        
        $defs = $factory->parse('aaa', 'double', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::FLOAT, $defs->getType());
        $this->assertEquals(['aaa' => Type::FLOAT], $defs->expandTypes('aaa', 12.34));
        $this->assertEquals(['aaa' => 12.34], $defs->expandValues('aaa', 12.34));
        
        $defs = $factory->parse('aaa', 'bool', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::BOOLEAN, $defs->getType());
        $this->assertEquals(['aaa' => Type::BOOLEAN], $defs->expandTypes('aaa', true));
        $this->assertEquals(['aaa' => true], $defs->expandValues('aaa', true));
        
        $defs = $factory->parse('aaa', '\DateTime', $reader);

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::DATETIME, $defs->getType());
        $this->assertEquals(['aaa' => Type::DATETIME], $defs->expandTypes('aaa', new \DateTime("2015/4/16")));
        $this->assertEquals(['aaa' => new \DateTime("2015/4/16")], $defs->expandValues('aaa', new \DateTime("2015/4/16")));
    }
    
    /**
     * @test
     */
    public function test_built_array() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
    
        $defs = $factory->parse('aaa', 'array', $reader);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::STRING, $defs->childDomain()->getType());
        $this->assertEquals([Type::STRING, Type::STRING, Type::STRING], $defs->expandTypes('', ['123', '456', 'qwy']));
        $this->assertEquals(['123', '456', 'qwy'], $defs->expandValues('', ['123', '456', 'qwy']));
    
        $defs = $factory->parse('aaa', 'string[]', $reader);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::STRING, $defs->childDomain()->getType());
        $this->assertEquals(
            ['f1' => Type::STRING, 'f2' => Type::STRING, 'f3' => Type::STRING], 
            $defs->expandTypes('', ['f1' => '123', 'f2' => '456', 'f3' => 'qwy'])
        );
        $this->assertEquals(['f1' => '123', 'f2' => '456', 'f3' => 'qwy'], $defs->expandValues('', ['f1' => '123', 'f2' => '456', 'f3' => 'qwy']));
    
        $defs = $factory->parse('aaa', 'int[]', $reader);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::INTEGER, $defs->childDomain()->getType());
        $this->assertEquals(['aaa_0' => Type::INTEGER, 'aaa_1' => Type::INTEGER, 'aaa_2' => Type::INTEGER], $defs->expandTypes('aaa', [123, 456, 789]));
        $this->assertEquals(['aaa_0' => 123, 'aaa_1' => 456, 'aaa_2' => 789], $defs->expandValues('aaa', [123, 456, 789]));
    
        $defs = $factory->parse('aaa', 'bool[]', $reader);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::BOOLEAN, $defs->childDomain()->getType());
        $this->assertEquals(
            ['aaa_f1' => Type::BOOLEAN, 'aaa_f2' => Type::BOOLEAN, 'aaa_f3' => Type::BOOLEAN], 
            $defs->expandTypes('aaa', ['f1' => false, 'f2' => false, 'f3' => true])
        );
        $this->assertEquals(
            ['aaa_f1' => false, 'aaa_f2' => false, 'aaa_f3' => true], 
            $defs->expandValues('aaa', ['f1' => false, 'f2' => false, 'f3' => true])
        );
    
        $defs = $factory->parse('aaa', 'int[][]', $reader);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs->childDomain());
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain()->childDomain());
        $this->assertEquals(Type::INTEGER, $defs->childDomain()->childDomain()->getType());
        $this->assertEquals(
            ['f1_0' => Type::INTEGER, 'f1_1' => Type::INTEGER, 'f2_0' => Type::INTEGER, 'f2_1' => Type::INTEGER], 
            $defs->expandTypes('', ['f1' => [123, 456], 'f2' => [789, 192]])
        );
        $this->assertEquals(
            ['f1_0' => 123, 'f1_1' => 456, 'f2_0' => 789, 'f2_1' => 192], 
            $defs->expandValues('', ['f1' => [123, 456], 'f2' => [789, 192]])
        );
    }
    
    /**
     * @test
     */
    public function test_built_custom_domain() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
    
        $defs = $factory->parse('aaa', '\Omelet\Tests\Target\Telephone', $reader);
        
        $this->assertInstanceOf(Domain\WrappedDomain::class, $defs);
        $this->assertEquals(['aaa' => Type::STRING], $defs->expandTypes('aaa', new Telephone("080-999-9999")));
        $this->assertEquals(['aaa' => "080-999-9999"], $defs->expandValues('aaa', new Telephone("080-999-9999")));
    }
    
    /**
     * @test
     */
    public function test_built_entity() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
    
        $defs = $factory->parse('aaa', '\Omelet\Tests\Target\Todo', $reader);
    
        $this->assertInstanceOf(Domain\ObjectDomain::class, $defs);
        
        $children = $defs->getChildren();
        
        $this->assertCount(4, $children);
        
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['id']);
        $this->assertEquals(Type::INTEGER, $children['id']->getType());
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['todo']);
        $this->assertEquals(Type::STRING, $children['todo']->getType());
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['created']);
        $this->assertEquals(Type::DATETIME, $children['created']->getType());
        $this->assertInstanceOf(Domain\WrappedDomain::class, $children['hidden']);
        
        $entity = Todo::__set_state(
            ['id' => 1024, 'todo' => 'test', 'created' => new \DateTime('2015/5/18 12:7:09'), 'hidden' => new Hidden(false)]
        );
        
        $this->assertEquals(
            ['aaa_id' => Type::INTEGER, 'aaa_todo' => Type::STRING, 'aaa_created' => Type::DATETIME, 'aaa_hidden' => Type::BOOLEAN], 
            $defs->expandTypes('aaa', $entity)
        );
        
        $this->assertEquals(
            ['aaa_id' => 1024, 'aaa_todo' => 'test', 'aaa_created' => new \DateTime('2015/5/18 12:7:09'), 'aaa_hidden' => false], 
            $defs->expandValues('aaa', $entity)
        );
    }
    
    /**
     * @test
     */
    public function test_built_complex_domain() {
        $factory = new DomainFactory();
        $reader = new AnnotationReader();
    
        $defs = new Domain\ComplexDomain([
            'obj' => $factory->parse('', '\Omelet\Tests\Target\Todo', $reader),
            'hoge' => $factory->parse('', 'int', $reader)
        ]);
        
        $children = $defs->getChildren();
        
        $this->assertCount(2, $children);
        
        $this->assertInstanceOf(Domain\ObjectDomain::class, $children['obj']);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['hoge']);
        
        $values = [
            'obj' => Todo::__set_state(
                ['id' => 1024, 'todo' => 'test', 'created' => new \DateTime('2015/5/18 12:7:09'), 'hidden' => new Hidden(true)]
            ),
            'hoge' => 4096
        ];
        
        $this->assertEquals(
            ['obj_id' => Type::INTEGER, 'obj_todo' => Type::STRING, 'obj_created' => Type::DATETIME, 'obj_hidden' => Type::BOOLEAN, 'hoge' => Type::INTEGER],
            $defs->expandTypes('', $values)
        );
        
        $this->assertEquals(
            ['obj_id' => 1024, 'obj_todo' => 'test', 'obj_created' => new \DateTime('2015/5/18 12:7:09'), 'obj_hidden' => true, 'hoge' => 4096], 
            $defs->expandValues('', $values)
        );
        
    }
}
