<?php

namespace OmeletTests;

use Doctrine\DBAL\Logging\SQLLogger;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Builder\DaoBuilder;

use Omelet\Domain\ComplexDomain;

use Omelet\Tests\Target\TodoDao;

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
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(0, $info['paramDomain']->getChildren());
        }
        select2: {
            $this->assertArrayHasKey('findById', $methods);
            
            $info = $methods['findById'];
            
            $this->assertArrayHasKey('name', $info);
            $this->assertEquals('findById', $info['name']);
            
            $this->assertArrayHasKey('type', $info);
            $this->assertInstanceOf('\Omelet\Annotation\Select', $info['type']);
            
            $this->assertArrayHasKey('paramDomain', $info);
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
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
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
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
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
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
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
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
            $this->assertInstanceOf(ComplexDomain::class, $info['paramDomain']);
            $this->assertCount(1, $info['paramDomain']->getChildren());
            $this->assertArrayHasKey('id', $info['paramDomain']->getChildren());
        }
    }
    
    private function exportDao(SQLLogger $logger = null) {
        @mkdir('tests/fixtures/exports', 755, true);
        @copy('tests/fixtures/todo.orig.sqlite3', 'tests/fixtures/todo.sqlite3');
        
        $context = new DaoBuilderContext([
            'sqlRootDir' => 'tests/fixtures/sql',
            'pdoDsn' => ['driver' => 'pdo_sqlite', 'path' => 'tests/fixtures/todo.sqlite3'],
        ]);
        $builder = new DaoBuilder(new \ReflectionClass(TodoDao::class), $context->getDaoClassName(TodoDao::class));
        
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
        $dao = $this->exportDao();
        
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
        $dao = $this->exportDao();
        
        $results = $dao->findById(2);
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
        $dao = $this->exportDao($logger);
        
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
        $dao = $this->exportDao($logger);
        
        $results = $dao->insert([
            'id' => 4,
            'todo' => 'test',
            'created' => '2015-7-7 12:12:07',
        ]);
        
        $results = $dao->listAll();
        $this->assertCount(4, $results);

        $row = $results[3];
        $this->assertEquals(4, $row['id']);
        $this->assertEquals("test", $row['todo']);
        $this->assertEquals(new \DateTime("2015/7/7 12:12:07"), new \DateTime($row['created']));
    }
}
