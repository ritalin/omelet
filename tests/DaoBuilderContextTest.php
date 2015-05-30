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
    public function test_validate_config_required_default() {
        $config = new Configuration;
        $results = $config->validate(true);

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('pdoDsn', $results);
    }

    /**
     * @test
     */
    public function test_validate_config_required() {
        $config = new Configuration;
        $config->daoClassPath = '';
        $results = $config->validate(true);

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('daoClassPath', $results);
        $this->assertArrayHasKey('pdoDsn', $results);
    }

    /**
     * @test
     */
    public function test_validate_config_invalid_value() {
        watchMode: {
            $config = new Configuration;
            $config->watchMode = 'hogeee';
            $results = $config->validate(true);

            $this->assertCount(2, $results);
            $this->assertArrayHasKey('watchMode', $results);
            $this->assertArrayHasKey('pdoDsn', $results);
        }
        paramCaseSensor: {
            $config = new Configuration;
            $config->paramCaseSensor = 'fooooo';
            $results = $config->validate(true);

            $this->assertCount(2, $results);
            $this->assertArrayHasKey('paramCaseSensor', $results);
            $this->assertArrayHasKey('pdoDsn', $results);
        }
        returnCaseSensor: {
            $config = new Configuration;
            $config->returnCaseSensor = 'wooooh';
            $results = $config->validate(true);

            $this->assertCount(2, $results);
            $this->assertArrayHasKey('returnCaseSensor', $results);
            $this->assertArrayHasKey('pdoDsn', $results);
        }
    }

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
