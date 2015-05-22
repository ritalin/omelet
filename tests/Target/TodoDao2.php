<?php

namespace Omelet\Tests\Target;

use Omelet\Annotation\Select;
use Omelet\Annotation\Insert;

use Omelet\Annotation\Dao;
use Omelet\Annotation\ParamAlt;
use Omelet\Annotation\Returning;

/**
 * @Dao(route="/")
 */
interface TodoDao2 {
    /**
     * @Select
     *
     * @param PrimaryKey key
     */
    function listById(PrimaryKey $key);
    
    /**
     * @Select
     *
     * @return Todo[] 
     */
    function listAll();
    
    /**
     * @Select
     *
     * @return array
     */
    function listAllAsRawArray();
    
    /**
     * @Select
     *
     * @param DateTime from
     * @param DateTime to
     * @return Todo[] 
     */
    function listByPub(\DateTime $from, \DateTime $to);

    /**
     * @Select
     *
     * @param PrimaryKey key
     * @return Todo
     */
    function findById(PrimaryKey $key);
    
    /**
     * @Select
     *
     * @param int key
     * @return bool
     */
    function exists($id);
    
    /**
     * @Select
     *
     * @param int key
     * @return Existance
     */
    function existsAsDomain($id);
    
    /**
     * @Select
     *
     * @return int[]
     */
    function primaryKeysDesc();
    
    /**
     * @Select
     *
     * @return PrimaryKey[]
     */
    function primaryKeysDescAsDomain();
    
    /**
     * @Insert
     *
     * @param Todo entity
     */
    function insert(Todo $entity);
}
