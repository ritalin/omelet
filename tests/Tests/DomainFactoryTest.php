<?php

namespace Omelet\Tests;

use Doctrine\DBAL\Types\Type;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Platforms\SqlitePlatform;

use Omelet\Domain;
use Omelet\Domain\DomainFactory;

use Omelet\Tests\Target\Telephone;
use Omelet\Tests\Target\Todo;
use Omelet\Tests\Target\Hidden;
use Omelet\Tests\Target\Editor;

use Omelet\Util\CaseSensor;

class DomainFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_void_domain()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('bbb', 'null', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\VoidDomain::class, $defs);
        $this->assertEquals(['bbb' => null], $defs->expandTypes(['bbb'], 'bbb', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(['bbb' => null], $defs->expandValues(['bbb'], 'bbb', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(null, $defs->convertResults('123', new SqlitePlatform(), CaseSensor::LowerSnake()));
    }
    
    /**
     * @test
     */
    public function test_built_in_domain()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('bbb', 'integer', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::INTEGER, $defs->getType());
        $this->assertEquals(['bbb' => Type::getType(Type::INTEGER)], $defs->expandTypes(['bbb'], 'bbb', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(['bbb' => 123], $defs->expandValues(['bbb'], 'bbb', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(123, $defs->convertResults('123', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('bbb', 'boolean', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::BOOLEAN, $defs->getType());
        $this->assertEquals(['bbb' => Type::getType(Type::BOOLEAN)], $defs->expandTypes(['bbb'], 'bbb', false, CaseSensor::LowerSnake()));
        $this->assertEquals(['bbb' => false], $defs->expandValues(['bbb'], 'bbb', false, CaseSensor::LowerSnake()));
        $this->assertEquals(true, $defs->convertResults('1', new SqlitePlatform(), CaseSensor::LowerSnake()));
        $this->assertEquals(false, $defs->convertResults('0', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('bbb', 'float', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::FLOAT, $defs->getType());
        $this->assertEquals(['bbb' => Type::getType(Type::FLOAT)], $defs->expandTypes(['bbb'], 'bbb', 98.7, CaseSensor::LowerSnake()));
        $this->assertEquals(['bbb' => 98.7], $defs->expandValues(['bbb'], 'bbb', 98.7, CaseSensor::LowerSnake()));
        $this->assertEquals(0.123, $defs->convertResults('0.123', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('bbb', 'string', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::STRING, $defs->getType());
        $this->assertEquals(['bbb' => Type::getType(Type::STRING)], $defs->expandTypes(['bbb'], 'bbb', 'qwerty', CaseSensor::LowerSnake()));
        $this->assertEquals(['bbb' => 'qwerty'], $defs->expandValues(['bbb'], 'bbb', 'qwerty', CaseSensor::LowerSnake()));
        $this->assertEquals('2345', $defs->convertResults(2345, new SqlitePlatform(), CaseSensor::LowerSnake()));
    }

    /**
     * @test
     */
    public function test_built_in_domain_alias()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('aaa', 'int', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::INTEGER, $defs->getType());
        $this->assertEquals(['aaa' => Type::getType(Type::INTEGER)], $defs->expandTypes(['aaa'], 'aaa', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(['aaa' => 123], $defs->expandValues(['aaa'], 'aaa', 123, CaseSensor::LowerSnake()));
        $this->assertEquals(123, $defs->convertResults('123', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('aaa', 'double', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::FLOAT, $defs->getType());
        $this->assertEquals(['aaa' => Type::getType(Type::FLOAT)], $defs->expandTypes(['aaa'], 'aaa', 12.34, CaseSensor::LowerSnake()));
        $this->assertEquals(['aaa' => 12.34], $defs->expandValues(['aaa'], 'aaa', 12.34, CaseSensor::LowerSnake()));
        $this->assertEquals(0.123, $defs->convertResults('0.123', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('aaa', 'bool', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::BOOLEAN, $defs->getType());
        $this->assertEquals(['aaa' => Type::getType(Type::BOOLEAN)], $defs->expandTypes(['aaa'], 'aaa', true, CaseSensor::LowerSnake()));
        $this->assertEquals(['aaa' => true], $defs->expandValues(['aaa'], 'aaa', true, CaseSensor::LowerSnake()));
        $this->assertEquals(true, $defs->convertResults('1', new SqlitePlatform(), CaseSensor::LowerSnake()));
        $this->assertEquals(false, $defs->convertResults('0', new SqlitePlatform(), CaseSensor::LowerSnake()));

        $defs = $factory->parse('aaa', '\DateTime', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs);
        $this->assertEquals(Type::DATETIME, $defs->getType());
        $this->assertEquals(['aaa' => Type::getType(Type::DATETIME)], $defs->expandTypes(['aaa'], 'aaa', new \DateTime('2015/4/16'), CaseSensor::LowerSnake()));
        $this->assertEquals(['aaa' => new \DateTime('2015/4/16')], $defs->expandValues(['aaa'], 'aaa', new \DateTime('2015/4/16'), CaseSensor::LowerSnake()));
        $this->assertEquals(new \DateTime('2015/4/16'), $defs->convertResults('2015/4/16', new SqlitePlatform(), CaseSensor::LowerSnake()));
    }

    /**
     * @test
     */
    public function test_array()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('aaa', 'array', CaseSensor::LowerSnake());

        $t = Type::getType(Type::STRING);

        $this->assertInstanceOf(Domain\AnyArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::STRING, $defs->childDomain()->getType());
        $this->assertEquals([$t, $t, $t], $defs->expandTypes([0, 1, 2], '', ['123', '456', 'qwy'], CaseSensor::LowerSnake()));
        $this->assertEquals(['123', '456', 'qwy'], $defs->expandValues([0, 1, 2], '', ['123', '456', 'qwy'], CaseSensor::LowerSnake()));

        $defs = $factory->parse('aaa', 'string[]', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::STRING, $defs->childDomain()->getType());
        $this->assertEquals(
            ['f1' => $t, 'f2' => $t, 'f3' => $t],
            $defs->expandTypes(['f1', 'f2', 'f3'], '', ['f1' => '123', 'f2' => '456', 'f3' => 'qwy'], CaseSensor::LowerSnake())
        );
        $this->assertEquals(
            ['f1' => '123', 'f2' => '456', 'f3' => 'qwy'],
            $defs->expandValues(['f1', 'f2', 'f3'], '', ['f1' => '123', 'f2' => '456', 'f3' => 'qwy'], CaseSensor::LowerSnake())
        );

        $defs = $factory->parse('aaa', 'int[]', CaseSensor::LowerSnake());
        $t = Type::getType(Type::INTEGER);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::INTEGER, $defs->childDomain()->getType());
        $this->assertEquals(
            ['aaa_0' => $t, 'aaa_1' => $t, 'aaa_2' => $t],
            $defs->expandTypes(['aaa_0', 'aaa_1', 'aaa_2'], 'aaa', [123, 456, 789], CaseSensor::LowerSnake())
        );
        $this->assertEquals(
            ['aaa_0' => 123, 'aaa_1' => 456, 'aaa_2' => 789],
            $defs->expandValues(['aaa_0', 'aaa_1', 'aaa_2'], 'aaa', [123, 456, 789], CaseSensor::LowerSnake())
        );

        $defs = $factory->parse('aaa', 'bool[]', CaseSensor::LowerSnake());
        $t = Type::getType(Type::BOOLEAN);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain());
        $this->assertEquals(Type::BOOLEAN, $defs->childDomain()->getType());
        $this->assertEquals(
            ['aaa_f1' => $t, 'aaa_f2' => $t, 'aaa_f3' => $t],
            $defs->expandTypes(['aaa_f1', 'aaa_f2', 'aaa_f3'], 'aaa', ['f1' => false, 'f2' => false, 'f3' => true], CaseSensor::LowerSnake())
        );
        $this->assertEquals(
            ['aaa_f1' => false, 'aaa_f2' => false, 'aaa_f3' => true],
            $defs->expandValues(['aaa_f1', 'aaa_f2', 'aaa_f3'], 'aaa', ['f1' => false, 'f2' => false, 'f3' => true], CaseSensor::LowerSnake())
        );

        $defs = $factory->parse('aaa', 'int[][]', CaseSensor::LowerSnake());
        $t = Type::getType(Type::INTEGER);

        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs);
        $this->assertInstanceOf(Domain\ArrayDomain::class, $defs->childDomain());
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $defs->childDomain()->childDomain());
        $this->assertEquals(Type::INTEGER, $defs->childDomain()->childDomain()->getType());
        $this->assertEquals(
            ['f1_0' => $t, 'f1_1' => $t, 'f2_0' => $t, 'f2_1' => $t],
            $defs->expandTypes(['f1_0', 'f1_1', 'f2_0', 'f2_1'], '', ['f1' => [123, 456], 'f2' => [789, 192]], CaseSensor::LowerSnake())
        );
        $this->assertEquals(
            ['f1_0' => 123, 'f1_1' => 456, 'f2_0' => 789, 'f2_1' => 192],
            $defs->expandValues(['f1_0', 'f1_1', 'f2_0', 'f2_1'], '', ['f1' => [123, 456], 'f2' => [789, 192]], CaseSensor::LowerSnake())
        );
    }

    /**
     * @test
     */
    public function test_built_custom_domain()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('aaa', '\Omelet\Tests\Target\Telephone', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\WrappedDomain::class, $defs);
        $this->assertEquals(
            ['aaa_number' => Type::getType(Type::STRING)],
            $defs->expandTypes(['aaa_number'], 'aaa', new Telephone('080-999-9999'), CaseSensor::LowerSnake())
        );
        $this->assertEquals(
            ['aaa_number' => '080-999-9999'],
            $defs->expandValues(['aaa_number'], 'aaa', new Telephone('080-999-9999'), CaseSensor::LowerSnake())
        );
    }

    /**
     * @test
     */
    public function test_built_entity()
    {
        $factory = new DomainFactory();

        $defs = $factory->parse('aaa', '\Omelet\Tests\Target\Todo', CaseSensor::LowerSnake());

        $this->assertInstanceOf(Domain\ObjectDomain::class, $defs);

        $children = $defs->getChildren();

        $this->assertCount(5, $children);

        $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['id']);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['id']->getDomain());
        $this->assertEquals(Type::INTEGER, $children['id']->getDomain()->getType());
        $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['todo']);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['todo']->getDomain());
        $this->assertEquals(Type::STRING, $children['todo']->getDomain()->getType());
        $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['created']);
        $this->assertInstanceOf(Domain\BuiltinDomain::class, $children['created']->getDomain());
        $this->assertEquals(Type::DATETIME, $children['created']->getDomain()->getType());
        $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['hidden']);
        $this->assertInstanceOf(Domain\WrappedDomain::class, $children['hidden']->getDomain());
        $this->assertEquals(Hidden::class, $children['hidden']->getDomain()->getType());
        $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['creator']);
        $this->assertInstanceOf(Domain\WrappedDomain::class, $children['creator']->getDomain());
        $this->assertEquals(Editor::class, $children['creator']->getDomain()->getType());

        $entity = Todo::__set_state(
            ['id' => 1024, 'todo' => 'test', 'created' => new \DateTime('2015/5/18 12:7:09'), 'hidden' => new Hidden(false)]
        );

        $this->assertEquals(
            [
                'aaa_id' => Type::getType(Type::INTEGER),
                'aaa_todo' => Type::getType(Type::STRING),
                'aaa_created' => Type::getType(Type::DATETIME),
                'aaa_hidden' => Type::getType(Type::BOOLEAN),
                'aaa_creator' => Type::getType(Type::STRING),
            ],
            $defs->expandTypes(['aaa_id', 'aaa_todo', 'aaa_created', 'aaa_hidden', 'aaa_creator'], 'aaa', $entity, CaseSensor::LowerSnake())
        );

        $this->assertEquals(
            ['aaa_id' => 1024, 'aaa_todo' => 'test', 'aaa_created' => new \DateTime('2015/5/18 12:7:09'), 'aaa_hidden' => false, 'aaa_creator' => null],
            $defs->expandValues(['aaa_id', 'aaa_todo', 'aaa_created', 'aaa_hidden', 'aaa_creator'], 'aaa', $entity, CaseSensor::LowerSnake())
        );
    }

    /**
     * @test
     */
    public function test_built_complex_domain()
    {
        $factory = new DomainFactory();

        $defs = new Domain\ComplexDomain([
            'obj' => $factory->parse('', '\Omelet\Tests\Target\Todo', CaseSensor::LowerSnake()),
            'hoge' => $factory->parse('', 'int', CaseSensor::LowerSnake())
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
            [
                'obj_id' => Type::getType(Type::INTEGER),
                'obj_todo' => Type::getType(Type::STRING),
                'obj_created' => Type::getType(Type::DATETIME),
                'obj_hidden' => Type::getType(Type::BOOLEAN),
                'obj_creator' => Type::getType(Type::STRING),
                'hoge' => Type::getType(Type::INTEGER)
            ],
            $defs->expandTypes(['obj_id', 'obj_todo', 'obj_created', 'obj_hidden', 'obj_creator', 'hoge'], '', $values, CaseSensor::LowerSnake())
        );

        $this->assertEquals(
            ['obj_id' => 1024, 'obj_todo' => 'test', 'obj_created' => new \DateTime('2015/5/18 12:7:09'), 'obj_hidden' => true, 'obj_creator' => null, 'hoge' => 4096],
            $defs->expandValues(['obj_id', 'obj_todo', 'obj_created', 'obj_hidden', 'obj_creator', 'hoge'], '', $values, CaseSensor::LowerSnake())
        );
    }
}
