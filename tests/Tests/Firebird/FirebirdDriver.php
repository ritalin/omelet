<?php

namespace Omelet\Tests\Firebird;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Connection;

class FirebirdDriver implements Driver
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        throw new \LogicException('connect is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new FirebirdPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        throw new \LogicException('getSchemaManager is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pdo_firebird';
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        throw new \LogicException('getDatabase is not Implemented.');
    }
}
