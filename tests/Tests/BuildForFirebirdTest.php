<?php

namespace Omelet\Tests;

use Doctrine\DBAL\Logging\SQLLogger;
use Omelet\Builder\Configuration;
use Omelet\Builder\DaoBuilder;
use Omelet\Builder\DaoBuilderContext;
use Omelet\Tests\Firebird\FirebirdDriver;
use Omelet\Tests\Target\ConstDao2;

define('PDO_FIREBIRD', 'pdo_firebird');

if (! extension_loaded(PDO_FIREBIRD)) {
    echo "Unloaded firebird driver";
    return;
}

class BuildForFirebirdTest extends \PHPUnit_Framework_TestCase
{
    private function exportDao($intf, SQLLogger $logger = null)
    {
        if (! file_exists('tests/fixtures/exports')) {
            @mkdir('tests/fixtures/exports', 0777, true);
        }

        $config = new Configuration;
        $values = [
            'sqlRootDir' => 'tests/fixtures/sql',
            'connectionString' => 'firebird:dbname=tests/fixtures/todo.fdb',
            'dialect' => 'firebird',
            'watchMode' => 'Always',
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
        
        $params = [
            'pdo' => new \PDO($context->getConfig()->connectionString, 'SYSDBA', 'masterkey'),
            'driverClass' => FirebirdDriver::class
        ];
        $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
        $conn->getConfiguration()->setSQLLogger($logger);

        return new $implClass($conn, $context);
    }

    /**
     * @test
     */
    public function test_select_returning_last_value_as_parameter()
    {
        $logger = null;
//        $logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
        $dao = $this->exportDao(ConstDao2::class, $logger);
        
        $result = $dao->returnAsParam();
        $this->assertCount(2, $result);
        $this->assertEquals([ 'value1' => 100, 'value2' => 19 ], $result);
    }
}
