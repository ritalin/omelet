<?php

namespace OmeletTests;

use Omelet\Builder\Configuration;
use Omelet\Builder\DaoBuilderContext;
use Omelet\Tests\Target\TodoDao;
use Omelet\Watch\WatchMode;
use Omelet\Util\CaseSensor;

class DaoBuilderContextTest extends \PHPUnit_Framework_TestCase {
    /**
     * @test
     */
     public function test_config() {
     
        $config = new Configuration;
        $config->daoClassPath = '_auto_generated';
        $config->sqlRootDir = 'queries';
        $config->pdoDsn = 'driver=pdo_sqlite&memory=true';
        $config->daoClassSuffix = 'Impl';
        $config->watchMode = 'Once';
        $config->returnCaseSensor = 'UpperCamel';
        
        $context = new DaoBuilderContext($config);

        $this->assertEquals($config, $context->getConfig());
     }
    
    /**
     * @test
     */
     public function test_dao_impl_class_name() {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->pdoDsn = 'driver=pdo_sqlite&memory=true'; }
        ));
        
        $this->assertEquals('Omelet\Tests\Target\TodoDaoImpl', $context->getDaoClassName(TodoDao::class));
     }
    
    /**
     * @test
     */
     public function test_db_connection_string() {
        $context = new DaoBuilderContext(new Configuration(
            function ($conf) { $conf->pdoDsn = 'driver=pdo_sqlite&memory=true'; }
        ));
        
        $this->assertEquals('driver=pdo_sqlite&memory=true', $context->connectionString());
     }
}
