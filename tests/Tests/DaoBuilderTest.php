<?php

namespace Omelet\Tests;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;

use Omelet\Builder\Configuration;
use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

use Omelet\Domain;
use Omelet\Domain\DomainFactory;

use Omelet\Sequence\DefaultSequenceStrategy;

use Omelet\Util\CaseSensor;

use Omelet\Tests\Target\TodoDao;
use Omelet\Tests\Target\TodoDao2;
use Omelet\Tests\Target\TodoDao3;
use Omelet\Tests\Target\ConstDao;
use Omelet\Tests\Target\Existance;
use Omelet\Tests\Target\PrimaryKey;
use Omelet\Tests\Target\Editor;
use Omelet\Tests\Target\Hidden;
use Omelet\Tests\Target\Timestamp;
use Omelet\Tests\Target\Todo;

class DaoBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_build_prepare()
    {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->connectionString = 'sqlite:///:memory:'; }
        ));
        $builder = new DaoBuilder(
            new \ReflectionClass(TodoDao::class), $context->getDaoClassName(TodoDao::class)
        );

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

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(0, $info['paramDomain']->getChildren());
        }
        select2: {
            $this->assertArrayHasKey('listById', $methods);

            $info = $methods['listById'];

            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('listById', $info['name']);

            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(1, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('id', $info['paramDomain']->getChildren());
        }
        select3: {
            $this->assertArrayHasKey('listByPub', $methods);

            $info = $methods['listByPub'];

            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('listByPub', $info['name']);

            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(2, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('from', $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('to', $info['paramDomain']->getChildren());
        }
        insert: {
            $this->assertArrayHasKey('insert', $methods);

            $info = $methods['insert'];

            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('insert', $info['name']);

            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Insert', $info['type']);

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(1, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('fields', $info['paramDomain']->getChildren());
        }
        update: {
            $this->assertArrayHasKey('update', $methods);

            $info = $methods['update'];

            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('update', $info['name']);

            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Update', $info['type']);

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(1, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('fields', $info['paramDomain']->getChildren());
        }
        delete: {
            $this->assertArrayHasKey('delete', $methods);

            $info = $methods['delete'];

            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('delete', $info['name']);

            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Delete', $info['type']);

            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(Domain\ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(1, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('id', $info['paramDomain']->getChildren());
        }
    }

    private function exportDao($intf, SQLLogger $logger = null)
    {
        if (! file_exists('tests/fixtures/exports')) {
            @mkdir('tests/fixtures/exports', 0777, true);
        }
        @copy('tests/fixtures/todo.orig.sqlite3', 'tests/fixtures/todo.sqlite3');

        $config = new Configuration;
        $values = [
            'sqlRootDir' => 'tests/fixtures/sql',
//            'connectionString' => 'sqlite:///tests/fixtures/todo.sqlite3', // For doctrine/DBAL bug
            'connectionString' => 'sqlite://localhost/?path=tests/fixtures/todo.sqlite3',
            'watchMode' => 'Always'
        ];
        foreach ($values as $f => $v) {
            $config->{$f} = $v;
        }
        $context = new DaoBuilderContext($config);
        $builder = new DaoBuilder(
            new \ReflectionClass($intf), $context->getDaoClassName($intf)
        );

        $builder->prepare();
        $c = $builder->export(true);

        $implClass = basename($builder->getClassName());
        $path = "tests/fixtures/exports/{$implClass}.php";
        file_put_contents($path, $c);

        require_once $path;

        $implClass = $builder->getClassName();
        $conn = \Doctrine\DBAL\DriverManager::getConnection(['url' => $context->getConfig()->connectionString]);
        $conn->getConfiguration()->setSQLLogger($logger);

        return new $implClass($conn, $context);
    }

    /**
     * @test
     */
    public function test_export_select_all()
    {
        $dao = $this->exportDao(TodoDao::class);

        $results = $dao->listAll();
        $this->assertCount(3, $results);

        $row = $results[0];
        $this->assertEquals(1, $row['id']);
        $this->assertEquals('aaa', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/01'), new \DateTime($row['created']));

        $row = $results[1];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals('bbb', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/11'), new \DateTime($row['created']));

        $row = $results[2];
        $this->assertEquals(3, $row['id']);
        $this->assertEquals('ccc', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/21 13:05:21'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_export_select_by_id()
    {
        $dao = $this->exportDao(TodoDao::class);

        $results = $dao->listById(2);
        $this->assertCount(1, $results);

        $row = $results[0];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals('bbb', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/11'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_export_select_with_range()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);

        $results = $dao->listByPub(new \DateTime('2015/4/30'), new \DateTime('2015/5/11'));
        $this->assertCount(2, $results);

        $row = $results[0];
        $this->assertEquals(1, $row['id']);
        $this->assertEquals('aaa', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/01'), new \DateTime($row['created']));

        $row = $results[1];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals('bbb', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/11'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_export_insert()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);

        $results = $dao->insert([
            'id' => 4,
            'todo' => 'test',
            'created' => '2015-7-7 12:12:07',
        ]);

        $this->assertEquals(1, $results);

        $results = $dao->listAll();
        $this->assertCount(4, $results);

        $row = $results[3];
        $this->assertEquals(4, $row['id']);
        $this->assertEquals('test', $row['todo']);
        $this->assertEquals(new \DateTime('2015/7/7 12:12:07'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_export_update()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);

        $results = $dao->update([
            'id' => 3,
            'todo' => 'change content...',
            'created' => '2015-7-7 10:10:10',
        ]);

        $this->assertEquals(1, $results);

        $results = $dao->listById(3);

        $row = $results[0];
        $this->assertEquals(3, $row['id']);
        $this->assertEquals('change content...', $row['todo']);
        $this->assertEquals(new \DateTime('2015-7-7 10:10:10'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_export_delete()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);

        $results = $dao->delete(2);

        $this->assertEquals(1, $results);

        $results = $dao->listById(2);

        $this->assertCount(0, $results);
    }

    /**
     * @test
     */
    public function test_select_all_returing_top_left()
    {
        $dao = $this->exportDao(TodoDao2::class);

        $value = $dao->listAllReturningTopLeft();
        $this->assertEquals(1, $value);
    }

    /**
     * @test
     */
    public function test_select_with_domain()
    {
        $dao = $this->exportDao(TodoDao2::class);

        $results = $dao->listById(new PrimaryKey(2));

        $this->assertCount(1, $results);

        $row = $results[0];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals('bbb', $row['todo']);
        $this->assertEquals(new \DateTime('2015/05/11'), new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_build_prepare_with_returning()
    {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->connectionString = 'sqlite:///:memory:'; }
        ));
        $builder = new DaoBuilder(
            new \ReflectionClass(TodoDao2::class), $context->getDaoClassName(TodoDao2::class)
        );

        $factory = new DomainFactory();

        $builder->prepare();
        $methods = $builder->getMethods();

        listById: {
            $info = $methods['listById'];

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\BuiltinDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals(Type::STRING, $info['returnDomain']->childDomain()->getType());
        }
        listAll: {
            $info = $methods['listAll'];
            $actial = $factory->parse('', Todo::class, CaseSensor::LowerSnake());

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals($actial, $info['returnDomain']->childDomain());
        }
        listAllAsRawArray: {
            $info = $methods['listAllAsRawArray'];
            $actial = $factory->parse('', Todo::class, CaseSensor::LowerSnake());

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\BuiltinDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals(Type::STRING, $info['returnDomain']->childDomain()->getType());
        }
        listByPub: {
            $info = $methods['listByPub'];
            $actial = $factory->parse('', Todo::class, CaseSensor::LowerSnake());

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals($actial, $info['returnDomain']->childDomain());
        }
        findById: {
            $info = $methods['findById'];
            $actial = $factory->parse('', Todo::class, CaseSensor::LowerSnake());

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);
            $this->assertEquals($actial, $info['returnDomain']);
        }
    }

    /**
     * @test
     */
    public function test_export_select_returning_primitive()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->exists(1);
        $this->assertSame(true, $results);

        $results = $dao->exists(999);
        $this->assertSame(false, $results);
    }

    /**
     * @test
     */
    public function test_export_select_returning_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->existsAsDomain(1);

        $this->assertInstanceOf(Existance::class, $results);
        $this->assertEquals(new Existance(true), $results);

        $results = $dao->existsAsDomain(999);

        $this->assertInstanceOf(Existance::class, $results);
        $this->assertEquals(new Existance(false), $results);
    }

    /**
     * @test
     */
    public function test_export_select_returning_primitive_array()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->primaryKeysDesc();

        $this->assertCount(3, $results);
        $this->assertEquals([3, 2, 1], $results);
    }

    /**
     * @test
     */
    public function test_select_returning_object_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(ConstDao::class, $logger);

        $results = $dao->now();

        $this->assertInstanceOf(Timestamp::class, $results);
        $this->assertEquals(new \DateTime('2015/10/10 12:13:59'), $results->getValue());
    }

    /**
     * @test
     */
    public function test_select_returning_domain_with_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(ConstDao::class, $logger);

        $results = $dao->hidden(new hidden(0));

        $this->assertInstanceOf(hidden::class, $results);
        $this->assertEquals(1, $results->getValue());
    }

    /**
     * @test
     */
    public function test_export_select_returning_domain_array()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->primaryKeysDescAsDomain();

        $this->assertCount(3, $results);
        $this->assertEquals([new PrimaryKey(3), new PrimaryKey(2), new PrimaryKey(1)], $results);
    }

    /**
     * @test
     */
    public function test_export_select_returning_entity()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->findById(new PrimaryKey(2));

        $this->assertInstanceOf(Todo::class, $results);
        $this->assertEquals(2, $results->id);
        $this->assertEquals('bbb', $results->todo);
        $this->assertEquals(new \DateTime('2015/05/11'), $results->created);
    }

    /**
     * @test
     */
    public function test_export_select_returning_entity_but_without_result()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->findById(new PrimaryKey(987));

        $this->assertNull($results);
    }

    /**
     * @test
     */
    public function test_export_select_returning_entity_array()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->listByPub(new \DateTime('2015/4/30'), new \DateTime('2015/5/11'));
        $this->assertCount(2, $results);

        $row = $results[0];
        $this->assertInstanceOf(Todo::class, $row);
        $this->assertEquals(1, $row->id);
        $this->assertEquals('aaa', $row->todo);
        $this->assertEquals(new \DateTime('2015/05/01'), $row->created);

        $row = $results[1];
        $this->assertInstanceOf(Todo::class, $row);
        $this->assertEquals(2, $row->id);
        $this->assertEquals('bbb', $row->todo);
        $this->assertEquals(new \DateTime('2015/05/11'), $row->created);
    }

    /**
     * @test
     */
    public function test_export_select_returning_entity_array_but_without_results()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->listByPub(new \DateTime('2010/4/30'), new \DateTime('2010/5/11'));
        $this->assertCount(0, $results);
    }

    /**
     * @test
     */
    public function test_export_insert_with_entity()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $entity = new Todo(function ($o) {
            $o->id = 13;
            $o->todo = 'insert with entity';
            $o->created = new \DateTime('2015-6-7 8:9:10');
        });
        $results = $dao->insert($entity);

        $this->assertEquals(1, $results);

        $results = $dao->listById(new PrimaryKey(13));

        $this->assertCount(1, $results);

        $row = $results[0];
        $this->assertEquals($entity->id, $row['id']);
        $this->assertEquals($entity->todo, $row['todo']);
        $this->assertEquals($entity->created, new \DateTime($row['created']));
    }

    /**
     * @test
     */
    public function test_prepare_dao_class_annottion()
    {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->connectionString = 'sqlite:///:memory:'; }
        ));
        $builder = new DaoBuilder(
            new \ReflectionClass(TodoDao2::class), $context->getDaoClassName(TodoDao2::class)
        );

        $factory = new DomainFactory();

        $config = $builder->getConfig();
        $this->assertCount(0, $config);

        $builder->prepare();
        $config = $builder->getConfig();

        $this->assertCount(1, $config);
        $this->assertEquals('/', $config['route']);
    }

    /**
     * @test
     */
    public function test_prepare_dao_returning_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
        $entity = $dao->findByIdReturningEntityWithDomain(new PrimaryKey(1));

        $this->assertInstanceOf(Todo::class, $entity);
        $this->assertEquals(new Hidden(1), $entity->hidden);
    }

    /**
     * @test
     */
    public function test_prepare_dao_returning_alias_field()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->findByIdReturningAlias(new PrimaryKey(2));

        $this->assertInstanceOf(Todo::class, $results);
        $this->assertEquals(2, $results->id);
        $this->assertEquals('bbb', $results->todo);
        $this->assertEquals(new \DateTime('2015/05/11'), $results->created);
    }

    /**
     * @test
     */
    public function test_select_returning_entity_default_value()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        $results = $dao->findById(new PrimaryKey(2));

        $this->assertInstanceOf(Todo::class, $results);
        $this->assertEquals(new Hidden(0), $results->hidden);
    }

    /**
     * @test
     */
    public function test_select_returning_entity_multi_args_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);

        keyonly: {
            $results = $dao->findByIdReturningEditorKeyOnly(new PrimaryKey(1));
            $this->assertInstanceOf(Todo::class, $results);
            $this->assertEquals(108, $results->creator->getId());
            $this->assertEquals('', $results->creator->getName());
        }
        keyvalue: {
            $results = $dao->findByIdReturningEditor(new PrimaryKey(1));
            $this->assertInstanceOf(Todo::class, $results);
            $this->assertEquals(108, $results->creator->getId());
            $this->assertEquals('Foo', $results->creator->getName());
        }
    }

    /**
     * @test
     */
    public function test_select_returning_multi_args_domain()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(ConstDao::class, $logger);

        $results = $dao->getEditorConst();
        $this->assertInstanceOf(Editor::class, $results);
        $this->assertEquals(1024, $results->getId());
        $this->assertEquals('Waaaah!!', $results->getName());
    }
    
    /**
     * @test
     */
    public function test_build_prepare_with_returning_case_sensitive()
    {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->connectionString = 'sqlite:///:memory:'; }
        ));
        $builder = new DaoBuilder(
            new \ReflectionClass(TodoDao2::class), $context->getDaoClassName(TodoDao2::class)
        );

        $factory = new DomainFactory();

        lowerSnake: {
            $builder->prepare();
            $methods = $builder->getMethods();

            $info = $methods['findByIdReturningEditor'];

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);

            $children = $info['returnDomain']->getChildren();

            $this->assertArrayHasKey('id', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['id']);
            $this->assertEquals('id', $children['id']->getName());
            $this->assertEquals(['todo_id'], $children['id']->getAlias());

            $this->assertArrayHasKey('creator', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['creator']);
            $this->assertEquals('creator', $children['creator']->getName());
            $this->assertEquals(['creator_id'], $children['creator']->getAlias());
            $this->assertEquals(['creator_name'], $children['creator']->getOptFields());
        }
        upperSnake: {
            $builder->setReturnCaseSensor(CaseSensor::UpperSnake());
            $builder->prepare();
            $methods = $builder->getMethods();

            $info = $methods['findByIdReturningEditor'];

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);

            $children = $info['returnDomain']->getChildren();

            $this->assertArrayHasKey('id', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['id']);
            $this->assertEquals('ID', $children['id']->getName());
            $this->assertEquals(['TODO_ID'], $children['id']->getAlias());

            $this->assertArrayHasKey('creator', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['creator']);
            $this->assertEquals('CREATOR', $children['creator']->getName());
            $this->assertEquals(['CREATOR_ID'], $children['creator']->getAlias());
            $this->assertEquals(['CREATOR_NAME'], $children['creator']->getOptFields());
        }
        lowerCamel: {
            $builder->setReturnCaseSensor(CaseSensor::LowerCamel());
            $builder->prepare();
            $methods = $builder->getMethods();

            $info = $methods['findByIdReturningEditor'];

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);

            $children = $info['returnDomain']->getChildren();

            $this->assertArrayHasKey('id', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['id']);
            $this->assertEquals('id', $children['id']->getName());
            $this->assertEquals(['todoId'], $children['id']->getAlias());

            $this->assertArrayHasKey('creator', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['creator']);
            $this->assertEquals('creator', $children['creator']->getName());
            $this->assertEquals(['creatorId'], $children['creator']->getAlias());
            $this->assertEquals(['creatorName'], $children['creator']->getOptFields());
        }
        upperCamel: {
            $builder->setReturnCaseSensor(CaseSensor::UpperCamel());
            $builder->prepare();
            $methods = $builder->getMethods();

            $info = $methods['findByIdReturningEditor'];

            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);

            $children = $info['returnDomain']->getChildren();

            $this->assertArrayHasKey('id', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['id']);
            $this->assertEquals('Id', $children['id']->getName());
            $this->assertEquals(['TodoId'], $children['id']->getAlias());

            $this->assertArrayHasKey('creator', $children);
            $this->assertInstanceOf(Domain\NamedAliasDomain::class, $children['creator']);
            $this->assertEquals('Creator', $children['creator']->getName());
            $this->assertEquals(['CreatorId'], $children['creator']->getAlias());
            $this->assertEquals(['CreatorName'], $children['creator']->getOptFields());
        }
    }

    /**
     * @test
     */
    public function test_sequence_name()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
        $this->assertNull($dao->sequenceName());

        $dao = $this->exportDao(TodoDao3::class, $logger);
        $this->assertEquals('todo', $dao->sequenceName());
    }
}
