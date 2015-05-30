# Omelet - SQL base object mapper for php.

This Library is nspired by Doma(https://github.com/domaframework/doma).

## Requirements

php >= 5.5.x

## Installation

Omelet can be installed with [Composer](https://getcomposer.org). 

Define the following requirement in your **composer.json** file:

```json
{
    "require": {
        "ritalin/omelet": "*"
    }
}
```

## Quick Start

1. Define Dao (Data Access Object) interface.
    * DAO method is need to describe an annotation comment to distingish database command/query.

    ```php
    use \Omelet\Annotation\Select;
    
    interface TodoDao {
        /**
         * @Select
         */
        function listAll();
    }
    ```

2. Instanciate **\Omelet\Builder\DaoBuilderContext** .
    * At least, need to a connection string to database as configuration.
    
    ```php
    $config = \Omelet\Builder\Configuration;
    $config->connectionString = "driver=pdo_sqlite&path=/path/to/todo.sqlite3";
    
    $context = new \Omelet\Builder\DaoBuilderContext($config);
    ```

3. Generate Dao concrete class.

    ```php
    $context->build(Todo::class);
    ```

4. Use Dao.
    * A Dao concrete class name note that 'Impl' is suffixed to interface name by default.
    
    ```php
    $conn = \Doctrine\DBAL\DriverManager->getConnection($context->getConfig()->connectionString);
    $dao = new TodoImpl($conn, $context);
    $rows = $dao->listAll();
    ```

## Sample Application

Please see [ritalin/omelet-bear-example](https://github.com/ritalin/omelet-bear-example) implemented with BEAR.Sunday (https://github.com/bearsunday/BEAR.Sunday) framework.

now work in progress ...
