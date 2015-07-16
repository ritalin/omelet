<?php

namespace Omelet\Tests\Firebird;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class FirebirdPlatform extends AbstractPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getBooleanTypeDeclarationSQL(array $columnDef)
    {
        throw new \LogicException('getBooleanTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegerTypeDeclarationSQL(array $columnDef)
    {
        throw new \LogicException('getIntegerTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBigIntTypeDeclarationSQL(array $columnDef)
    {
        throw new \LogicException('getBigIntTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getSmallIntTypeDeclarationSQL(array $columnDef)
    {
        throw new \LogicException('getSmallIntTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    protected function _getCommonIntegerTypeDeclarationSQL(array $columnDef)
    {
        throw new \LogicException('_getCommonIntegerTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeDoctrineTypeMappings()
    {
        throw new \LogicException('initializeDoctrineTypeMappings is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getClobTypeDeclarationSQL(array $field)
    {
        throw new \LogicException('getClobTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlobTypeDeclarationSQL(array $field)
    {
        throw new \LogicException('getBlobTypeDeclarationSQL is not Implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        throw new \LogicException('getName is not Implemented.');
    }
}
