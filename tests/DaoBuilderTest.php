<?php

namespace OmeletTests;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

use Omelet\Domain;
use Omelet\Domain\DomainFactory;

use Omelet\Tests\Target\TodoDao;
use Omelet\Tests\Target\TodoDao2;
use Omelet\Tests\Target\Existance;
use Omelet\Tests\Target\PrimaryKey;
use Omelet\Tests\Target\Todo;

class DaoBuilderTest extends \PHPUnit_Framework_TestCase {
    /**
     * @Test
     */
     public function test_build_prepare() {
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
    
    private function exportDao($intf, SQLLogger $logger = null) {
        @mkdir('tests/fixtures/exports', 755, true);
        @copy('tests/fixtures/todo.orig.sqlite3', 'tests/fixtures/todo.sqlite3');
        
        $context = new DaoBuilderContext([
            'sqlRootDir' => 'tests/fixtures/sql',
            'pdoDsn' => ['driver' => 'pdo_sqlite', 'path' => 'tests/fixtures/todo.sqlite3'],
        ]);
        $builder = new DaoBuilder(new \ReflectionClass($intf), $context->getDaoClassName($intf));
        
        $builder->prepare();
        $c = $builder->export(true);
        
        $implClass = basename($builder->getClassName());
        $path = "tests/fixtures/exports/{$implClass}.php";
        file_put_contents($path, $c);
        
        require_once $path;
      
        $implClass = $builder->getClassName();
        $conn = \Doctrine\DBAL\DriverManager::getConnection($context->getConfig()['pdoDsn']);
        $conn->getConfiguration()->setSQLLogger($logger);
        
        return new $implClass($conn, $context);
    }
    
    /**
     * @Test
     */
    public function test_export_select_all() {
        $dao = $this->exportDao(TodoDao::class);
        
        $results = $dao->listAll();
        $this->assertCount(3, $results);

        $row = $results[0];
        $this->assertEquals(1, $row['id']);
        $this->assertEquals("aaa", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/01"), new \DateTime($row['created']));

        $row = $results[1];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals("bbb", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/11"), new \DateTime($row['created']));

        $row = $results[2];
        $this->assertEquals(3, $row['id']);
        $this->assertEquals("ccc", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/21 13:05:21"), new \DateTime($row['created']));
    }
    
    /**
     * @Test
     */
    public function test_export_select_by_id() {
        $dao = $this->exportDao(TodoDao::class);
        
        $results = $dao->listById(2);
        $this->assertCount(1, $results);

        $row = $results[0];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals("bbb", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/11"), new \DateTime($row['created']));
    }
    
    /**
     * @Test
     */
    public function test_export_select_with_range() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);
        
        $results = $dao->listByPub(new \DateTime('2015/4/30'), new \DateTime('2015/5/11'));
        $this->assertCount(2, $results);

        $row = $results[0];
        $this->assertEquals(1, $row['id']);
        $this->assertEquals("aaa", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/01"), new \DateTime($row['created']));

        $row = $results[1];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals("bbb", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/11"), new \DateTime($row['created']));
    }
    
    /**
     * @Test
     */
    public function test_export_insert() {
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
        $this->assertEquals("test", $row['todo']);
        $this->assertEquals(new \DateTime("2015/7/7 12:12:07"), new \DateTime($row['created']));
    }
    
    /**
     * @Test
     */
    public function test_export_update() {
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
        $this->assertEquals("change content...", $row['todo']);
        $this->assertEquals(new \DateTime("2015-7-7 10:10:10"), new \DateTime($row['created']));
    }
    
    /**
     * @Test
     */
    public function test_export_delete() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao::class, $logger);
    
        $results = $dao->delete(2);
        
        $this->assertEquals(1, $results);
        
        $results = $dao->listById(2);
        
        $this->assertCount(0, $results);
    }
    
    /**
     * @Test
     */
    public function test_export_select_with_domain() {
        $dao = $this->exportDao(TodoDao2::class);
    
        $results = $dao->listById(new PrimaryKey(2));
        
        $this->assertCount(1, $results);

        $row = $results[0];
        $this->assertEquals(2, $row['id']);
        $this->assertEquals("bbb", $row['todo']);
        $this->assertEquals(new \DateTime("2015/05/11"), new \DateTime($row['created']));
    }

    /**
     * @Test
     */
    public function test_build_prepare_with_returning() {
        $context = new DaoBuilderContext();
        $builder = new DaoBuilder(new \ReflectionClass(TodoDao2::class), $context->getDaoClassName(TodoDao2::class));
        
        $reader = new AnnotationReader();
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
            $actial = $factory->parse('', Todo::class, $reader);
        
            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals($actial, $info['returnDomain']->childDomain());
        }
        listAllAsRawArray: {
            $info = $methods['listAllAsRawArray'];
            $actial = $factory->parse('', Todo::class, $reader);
        
            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\BuiltinDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals(Type::STRING, $info['returnDomain']->childDomain()->getType());
        }
        listByPub: {
            $info = $methods['listByPub'];
            $actial = $factory->parse('', Todo::class, $reader);
        
            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ArrayDomain::class, $info['returnDomain']);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']->childDomain());
            $this->assertEquals($actial, $info['returnDomain']->childDomain());
        }
        findById: {
            $info = $methods['findById'];
            $actial = $factory->parse('', Todo::class, $reader);
        
            $this->assertArrayHasKey('returnDomain', $info);
            $this->assertInstanceOf(Domain\ObjectDomain::class, $info['returnDomain']);
            $this->assertEquals($actial, $info['returnDomain']);
        }
    }
   
    /**
     * @Test
     */
    public function test_export_select_returning_primitive() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
    
        $results = $dao->exists(1);
        $this->assertSame(true, $results);
    
        $results = $dao->exists(999);
        $this->assertSame(false, $results);
    }
   
    /**
     * @Test
     */
    public function test_export_select_returning_domain() {
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
     * @Test
     */
    public function test_export_select_returning_primitive_array() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
    
        $results = $dao->primaryKeysDesc();

        $this->assertCount(3, $results);
        $this->assertEquals([3, 2, 1], $results);
    }
   
    /**
     * @Test
     */
    public function test_export_select_returning_domain_array() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
    
        $results = $dao->primaryKeysDescAsDomain();

        $this->assertCount(3, $results);
        $this->assertEquals([new PrimaryKey(3), new PrimaryKey(2), new PrimaryKey(1)], $results);
    }
    /**
     * @Test
     */
    public function test_export_select_returning_entity() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
    
        $results = $dao->findById(new PrimaryKey(2));
        
        $this->assertInstanceOf(Todo::class, $results);        
        $this->assertEquals(2, $results->id);
        $this->assertEquals("bbb", $results->todo);
        $this->assertEquals(new \DateTime("2015/05/11"), $results->created);
    }
    
    /**
     * @Test
     */
    public function test_export_select_returning_entity_array() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
    
        $results = $dao->listByPub(new \DateTime('2015/4/30'), new \DateTime('2015/5/11'));
        $this->assertCount(2, $results);

        $row = $results[0];
        $this->assertInstanceOf(Todo::class, $row);
        $this->assertEquals(1, $row->id);
        $this->assertEquals("aaa", $row->todo);
        $this->assertEquals(new \DateTime("2015/05/01"), $row->created);

        $row = $results[1];
        $this->assertInstanceOf(Todo::class, $row);
        $this->assertEquals(2, $row->id);
        $this->assertEquals("bbb", $row->todo);
        $this->assertEquals(new \DateTime("2015/05/11"), $row->created);
    }
    
    /**
     * @Test
     */
    public function test_export_insert_with_entity() {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(TodoDao2::class, $logger);
        
        $entity = new Todo(function($o) {
            $o->id = 13;
            $o->todo = "insert with entity";
            $o->created = new \DateTime("2015-6-7 8:9:10");
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
}
