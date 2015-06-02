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
     * @return integer
     */
    function listAllReturningTopLeft();
    
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
     * @param PrimaryKey key
     * @return Todo
     */
    function findByIdReturningEntityWithDomain(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     * @return Todo
     */
    function findByIdReturningAlias(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     * @return Todo
     */
    function findByIdReturningEditorKeyOnly(PrimaryKey $key);

    /**
     * @Select
     *
     * @param PrimaryKey key
     * @return Todo
     */
    function findByIdReturningEditor(PrimaryKey $key);
    
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
