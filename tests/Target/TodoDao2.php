<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;

use Omelet\Annotation\ParamAlt;

interface TodoDao2 {
    /**
     * @Select
     * @ParamAlt(type="Omelet\Tests\Target\PrimaryKey", name="key")
     * @param PrimaryKey key
     */
    function listById(PrimaryKey $key);
    
    /**
     * @Insert
     * @ParamAlt(type="Omelet\Tests\Target\Todo", name="entity")
     * @param Todo entity
     */
//    function insert(array $entity);
}
