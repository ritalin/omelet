<?php

namespace OmeletTests;

use Omelet\Builder\DaoBuilderContext;
use Omelet\Tests\Target\TodoDao;
use Omelet\Watch\WatchMode;

class DaoBuilderContextTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_default_config() {
        $context = new DaoBuilderContext();
        
        $expects = [
            'daoClassPath' => '_auto_generated',
            'sqlRootDir' => 'sql',
            'pdoDsn' => [],
            'daoClassSuffix' => 'Impl',
            'watchMode' => WatchMode::Whenever(),
        ];
        
        $this->assertEquals($expects, $context->getConfig());
    }
    
    /**
     * @test
     */
     public function test_config() {
        $context = new DaoBuilderContext(['sqlRootDir' => 'queries', 'watchMode' => WatchMode::Once()]);
     
        $expects = [
            'daoClassPath' => '_auto_generated',
            'sqlRootDir' => 'queries',
            'pdoDsn' => [],
            'daoClassSuffix' => 'Impl',
            'watchMode' => WatchMode::Once(),
        ];
        
        $this->assertEquals($expects, $context->getConfig());
     }
    
    /**
     * @test
     */
     public function test_dao_impl_class_name() {
        $context = new DaoBuilderContext();
        
        $this->assertEquals('Omelet\Tests\Target\TodoDaoImpl', $context->getDaoClassName(TodoDao::class));
     }
    
    /**
     * @test
     */
     public function test_db_connection_string() {
        $context = new DaoBuilderContext(['pdoDsn' => ['driver' => 'pdo_sqlite', 'memory' => 'true']]);
        
        $this->assertEquals('driver=pdo_sqlite&memory=true', $context->connectionString());
     }
}
